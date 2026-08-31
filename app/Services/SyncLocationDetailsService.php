<?php

namespace App\Services;

use App\Models\GhlAppointment;
use App\Models\GhlCalendar;
use App\Models\GhlContact;
use App\Models\GhlCustomField;
use App\Models\GhlLocation;
use App\Models\GhlOpportunity;
use App\Models\GhlTag;
use App\Models\GhlUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncLocationDetailsService
{

    public static function syncLocationDetails(GhlLocation $location)
    {
        $ghlResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $location->access_token,
            'Version' => '2021-07-28',
        ])->get("https://services.leadconnectorhq.com/locations/{$location->ghl_location_id}");

        if ($ghlResponse->successful()) {
            $details = $ghlResponse->json()['location'] ?? [];

            $location->update([
                'name' => $details['name'] ?? $location->name,
                'email' => $details['email'] ?? $location->email,
                'phone' => $details['phone'] ?? $location->phone,
                'address' => $details['address'] ?? $location->address,
                'city' => $details['city'] ?? $location->city,
                'state' => $details['state'] ?? $location->state,
                'country' => $details['country'] ?? $location->country,
                'timezone' => $details['timezone'] ?? $location->timezone,
            ]);
        }
    }

    public static function syncUsers(GhlLocation $location): int
    {
        $ghlResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $location->access_token,
            'Version' => '2021-07-28',
        ])->get("https://services.leadconnectorhq.com/users/?locationId={$location->ghl_location_id}");

        if (!$ghlResponse->successful()) {
            throw new \RuntimeException('GHL users sync failed: ' . $ghlResponse->body());
        }

        $syncedUsers = 0;

        foreach ($ghlResponse->json('users', []) as $userData) {
            if (empty($userData['id'])) {
                continue;
            }

            GhlUser::updateOrCreate(
                ['ghl_user_id' => $userData['id']],
                [
                    'user_id' => $location->user_id,
                    'ghl_location_ids' => $userData['locationIds'] ?? [$location->ghl_location_id],
                    'first_name' => $userData['firstName'] ?? null,
                    'last_name' => $userData['lastName'] ?? null,
                    'email' => $userData['email'] ?? null,
                    'phone' => $userData['phone'] ?? null,
                    'type' => $userData['type'] ?? null,
                    'role' => $userData['role'] ?? null,
                    'permissions' => $userData['permissions'] ?? null,
                ]
            );

            $syncedUsers++;
        }

        return $syncedUsers;
    }

    public static function syncContacts(GhlLocation $location): int
    {
        $ghlResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $location->access_token,
            'Version' => '2021-07-28',
            'Accept' => 'application/json',
        ])->get('https://services.leadconnectorhq.com/contacts/', [
            'locationId' => $location->ghl_location_id,
            'limit' => 100,
        ]);

        if (!$ghlResponse->successful()) {
            throw new \RuntimeException('GHL contacts sync failed: ' . $ghlResponse->body());
        }

        $syncedContacts = 0;

        foreach ($ghlResponse->json('contacts', []) as $contactData) {
            $contactId = $contactData['id'] ?? $contactData['_id'] ?? $contactData['contactId'] ?? null;

            if (empty($contactId)) {
                continue;
            }

            GhlContact::updateOrCreate(
                [
                    'ghl_contact_id' => (string) $contactId,
                    'ghl_location_id' => (string) $location->ghl_location_id,
                ],
                [
                    'user_id' => $location->user_id,
                    'first_name' => $contactData['firstName'] ?? null,
                    'last_name' => $contactData['lastName'] ?? null,
                    'name' => $contactData['contactName'] ?? $contactData['name'] ?? null,
                    'email' => $contactData['email'] ?? null,
                    'phone' => $contactData['phone'] ?? null,
                    'city' => $contactData['city'] ?? null,
                    'state' => $contactData['state'] ?? null,
                    'country' => $contactData['country'] ?? null,
                    'postal_code' => $contactData['postalCode'] ?? null,
                    'company' => $contactData['companyName'] ?? null,
                    'assigned_to_ghl_user' => $contactData['assignedTo'] ?? null,
                    'source' => $contactData['source'] ?? null,
                    'tags' => $contactData['tags'] ?? [],
                    'custom_fields' => $contactData['customFields'] ?? [],
                    'ghl_company_id' => $location->ghl_company_id,
                ]
            );

            $syncedContacts++;

            foreach ($contactData['tags'] ?? [] as $tagValue) {
                if (is_string($tagValue)) {
                    $tagId = $tagValue;
                    $tagName = $tagValue;
                } elseif (is_array($tagValue)) {
                    $tagId = $tagValue['id'] ?? $tagValue['_id'] ?? $tagValue['tagId'] ?? null;
                    $tagName = $tagValue['name'] ?? $tagValue['tagName'] ?? null;
                } else {
                    continue;
                }

                if (empty($tagId) && empty($tagName)) {
                    continue;
                }

                $normalizedTagId = (string) ($tagId ?? $tagName);
                $normalizedTagName = (string) ($tagName ?? $normalizedTagId);

                $tagRecord = GhlTag::where('ghl_location_id', (string) $location->ghl_location_id)
                    ->where(function ($query) use ($normalizedTagId, $normalizedTagName) {
                        $query->where('ghl_tag_id', $normalizedTagId)
                            ->orWhere('name', $normalizedTagName);
                    })
                    ->first();

                if ($tagRecord) {
                    $tagRecord->update([
                        'ghl_tag_id' => $tagRecord->ghl_tag_id ?: $normalizedTagId,
                        'name' => $normalizedTagName,
                    ]);
                } else {
                    $tagRecord = GhlTag::create([
                        'ghl_location_id' => (string) $location->ghl_location_id,
                        'ghl_tag_id' => $normalizedTagId,
                        'name' => $normalizedTagName,
                    ]);
                }

                DB::table('ghl_contact_tags')->updateOrInsert(
                    [
                        'ghl_location_id' => (string) $location->ghl_location_id,
                        'ghl_contact_id' => (string) $contactId,
                        'ghl_tag_id' => $tagRecord->ghl_tag_id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            foreach ($contactData['customFields'] ?? [] as $fieldData) {
                $fieldId = $fieldData['id'] ?? $fieldData['fieldId'] ?? $fieldData['_id'] ?? null;

                if (empty($fieldId)) {
                    continue;
                }

                $fieldValue = $fieldData['value'] ?? null;

                GhlCustomField::updateOrCreate(
                    [
                        'ghl_location_id' => (string) $location->ghl_location_id,
                        'ghl_field_id' => (string) $fieldId,
                    ],
                    [
                        'name' => $fieldData['name'] ?? null,
                        'field_key' => $fieldData['fieldKey'] ?? $fieldData['key'] ?? null,
                        'data_type' => $fieldData['dataType'] ?? null,
                        'options' => $fieldData['options'] ?? null,
                    ]
                );

                DB::table('ghl_contact_custom_fields')->updateOrInsert(
                    [
                        'ghl_location_id' => (string) $location->ghl_location_id,
                        'ghl_contact_id' => (string) $contactId,
                        'ghl_field_id' => (string) $fieldId,
                    ],
                    [
                        'value' => is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return $syncedContacts;
    }

    public static function syncOpportunities(GhlLocation $location): int
    {
        $ghlResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $location->access_token,
            'Version' => '2021-07-28',
            'Accept' => 'application/json',
        ])->get('https://services.leadconnectorhq.com/opportunities/search', [
            'location_id' => $location->ghl_location_id,
            'limit' => 100,
        ]);

        if (!$ghlResponse->successful()) {
            throw new \RuntimeException('GHL opportunities sync failed: ' . $ghlResponse->body());
        }

        $savedCount = 0;

        foreach ($ghlResponse->json('opportunities', []) as $oppData) {
            $oppId = $oppData['id'] ?? $oppData['_id'] ?? $oppData['opportunityId'] ?? null;

            if (empty($oppId)) {
                continue;
            }

            GhlOpportunity::updateOrCreate(
                [
                    'ghl_opportunity_id' => (string) $oppId,
                    'ghl_location_id' => (string) $location->ghl_location_id,
                ],
                [
                    'user_id' => $location->user_id,
                    'ghl_pipeline_id' => $oppData['pipelineId'] ?? null,
                    'ghl_pipeline_stage_id' => $oppData['pipelineStageId'] ?? $oppData['stageId'] ?? null,
                    'ghl_contact_id' => $oppData['contactId'] ?? null,
                    'name' => $oppData['name'] ?? 'Unnamed Opportunity',
                    'status' => $oppData['status'] ?? 'open',
                    'monetary_value' => $oppData['monetaryValue'] ?? 0,
                    'assigned_to_ghl_user' => $oppData['assignedTo'] ?? null,
                    'source' => $oppData['source'] ?? null,
                    'date_added' => $oppData['dateAdded'] ?? $oppData['date_added'] ?? null,
                ]
            );

            $savedCount++;
        }

        return $savedCount;
    }

    public static function upsertAppointmentFromWebhook(array $appointmentData, GhlLocation $location): bool
    {
        $appointmentId = $appointmentData['id'] ?? $appointmentData['_id'] ?? $appointmentData['appointmentId'] ?? null;

        if (empty($appointmentId)) {
            return false;
        }

        $contactId = $appointmentData['contactId']
            ?? $appointmentData['contact_id']
            ?? ($appointmentData['contact'] ?? null)['id'] ?? ($appointmentData['contact'] ?? null)['_id'] ?? null;

        $payload = [
            'user_id' => $location->user_id,
            'ghl_location_id' => (string) $location->ghl_location_id,
            'ghl_company_id' => $location->ghl_company_id,
            'ghl_contact_id' => $contactId,
            'calendar_id' => $appointmentData['calendarId'] ?? $appointmentData['calendar_id'] ?? null,
            'assigned_to_ghl_user' => $appointmentData['assignedTo'] ?? $appointmentData['assigned_to'] ?? null,
            'title' => $appointmentData['title'] ?? $appointmentData['name'] ?? null,
            'address' => $appointmentData['address'] ?? null,
            'status' => $appointmentData['status'] ?? null,
            'ghl_group_id' => $appointmentData['groupId'] ?? $appointmentData['group_id'] ?? null,
            'users' => $appointmentData['users'] ?? $appointmentData['guests'] ?? [],
            'notes' => $appointmentData['notes'] ?? $appointmentData['description'] ?? null,
            'source' => $appointmentData['source'] ?? null,
            'start_time' => $appointmentData['startTime'] ?? $appointmentData['start_time'] ?? $appointmentData['startDateTime'] ?? null,
            'end_time' => $appointmentData['endTime'] ?? $appointmentData['end_time'] ?? $appointmentData['endDateTime'] ?? null,
            'date_added' => $appointmentData['dateAdded'] ?? $appointmentData['date_added'] ?? null,
            'date_updated' => $appointmentData['dateUpdated'] ?? $appointmentData['date_updated'] ?? now(),
        ];

        GhlAppointment::updateOrCreate(
            ['ghl_appointment_id' => (string) $appointmentId],
            $payload
        );

        return true;
    }

    public static function syncAppointments(GhlLocation $location): int
    {
        $calendarsResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $location->access_token,
            'Version' => '2021-07-28',
            'Accept' => 'application/json',
        ])->get('https://services.leadconnectorhq.com/calendars', [
            'locationId' => $location->ghl_location_id,
        ]);

        if (!$calendarsResponse->successful()) {
            logger()->warning('GHL calendars sync skipped', [
                'status' => $calendarsResponse->status(),
                'body' => $calendarsResponse->body(),
                'location_id' => $location->ghl_location_id,
            ]);

            return 0;
        }

        $savedCount = 0;
        $calendars = $calendarsResponse->json('calendars', $calendarsResponse->json('data', []));

        if (isset($calendars['calendars']) && is_array($calendars['calendars'])) {
            $calendars = $calendars['calendars'];
        }

        if (isset($calendars['items']) && is_array($calendars['items'])) {
            $calendars = $calendars['items'];
        }

        if (!is_array($calendars)) {
            return 0;
        }

        if (array_is_list($calendars) === false && isset($calendars['id'])) {
            $calendars = [$calendars];
        }

        foreach ($calendars as $calendarData) {
            if (!is_array($calendarData)) {
                continue;
            }

            $calendarId = $calendarData['id'] ?? $calendarData['_id'] ?? $calendarData['calendarId'] ?? $calendarData['calendar_id'] ?? null;

            if (empty($calendarId)) {
                continue;
            }

            GhlCalendar::updateOrCreate(
                [
                    'ghl_location_id' => (string) $location->ghl_location_id,
                    'ghl_calendar_id' => (string) $calendarId,
                ],
                [
                    'name' => $calendarData['name'] ?? null,
                    'description' => $calendarData['description'] ?? null,
                ]
            );

            $eventsResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $location->access_token,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->get("https://services.leadconnectorhq.com/calendars/{$calendarId}/events", [
                'locationId' => $location->ghl_location_id,
            ]);

            if (!$eventsResponse->successful()) {
                continue;
            }

            $eventPayload = $eventsResponse->json('events', $eventsResponse->json('appointments', $eventsResponse->json('data', [])));

            if (is_array($eventPayload) && isset($eventPayload['events']) && is_array($eventPayload['events'])) {
                $eventPayload = $eventPayload['events'];
            }

            if (is_array($eventPayload) && isset($eventPayload['appointments']) && is_array($eventPayload['appointments'])) {
                $eventPayload = $eventPayload['appointments'];
            }

            if (is_array($eventPayload) && isset($eventPayload['items']) && is_array($eventPayload['items'])) {
                $eventPayload = $eventPayload['items'];
            }

            $eventItems = is_array($eventPayload) ? $eventPayload : [];

            foreach ($eventItems as $eventData) {
                if (!is_array($eventData)) {
                    continue;
                }

                $eventId = $eventData['id'] ?? $eventData['_id'] ?? $eventData['eventId'] ?? $eventData['appointmentId'] ?? $eventData['appointment_id'] ?? null;

                if (empty($eventId)) {
                    continue;
                }

                $contactId = $eventData['contactId']
                    ?? $eventData['contact_id']
                    ?? $eventData['contact']['id'] ?? $eventData['contact']['_id'] ?? $eventData['contactId'] ?? null;

                if (is_array($eventData['contact'] ?? null) && empty($contactId)) {
                    $contactId = $eventData['contact']['id'] ?? $eventData['contact']['_id'] ?? null;
                }

                GhlAppointment::updateOrCreate(
                    [
                        'ghl_appointment_id' => (string) $eventId,
                    ],
                    [
                        'user_id' => $location->user_id,
                        'ghl_location_id' => (string) $location->ghl_location_id,
                        'ghl_company_id' => $location->ghl_company_id,
                        'ghl_contact_id' => $contactId,
                        'calendar_id' => (string) $calendarId,
                        'assigned_to_ghl_user' => $eventData['assignedTo'] ?? $eventData['assigned_to'] ?? null,
                        'title' => $eventData['title'] ?? $eventData['name'] ?? null,
                        'address' => $eventData['address'] ?? null,
                        'status' => $eventData['status'] ?? null,
                        'ghl_group_id' => $eventData['groupId'] ?? $eventData['group_id'] ?? null,
                        'users' => $eventData['users'] ?? $eventData['guests'] ?? [],
                        'notes' => $eventData['notes'] ?? $eventData['description'] ?? null,
                        'source' => $eventData['source'] ?? null,
                        'start_time' => $eventData['startTime'] ?? $eventData['start_time'] ?? $eventData['startDateTime'] ?? null,
                        'end_time' => $eventData['endTime'] ?? $eventData['end_time'] ?? $eventData['endDateTime'] ?? null,
                        'date_added' => $eventData['dateAdded'] ?? $eventData['date_added'] ?? null,
                        'date_updated' => $eventData['dateUpdated'] ?? $eventData['date_updated'] ?? null,
                    ]
                );

                $savedCount++;
            }
        }

        return $savedCount;
    }
}