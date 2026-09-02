<?php

namespace App\Services;

use App\Models\GhlAppointment;
use App\Models\GhlCalendar;
use App\Models\GhlContact;
use App\Models\GhlCustomField;
use App\Models\GhlLocation;
use App\Models\GhlOpportunity;
use App\Models\GhlTag;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\GhlUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncLocationDetailsService
{

    public static function syncConversations(GhlLocation $location): int
    {
        $response = self::conversationRequest($location, 'https://services.leadconnectorhq.com/conversations/search', [
            'locationId' => $location->ghl_location_id,
            'limit' => 100,
        ]);
        $syncedMessages = 0;

        foreach (($response['conversations'] ?? $response['data'] ?? []) as $conversationData) {
            if (is_string($conversationData)) {
                $conversationData = ['id' => $conversationData];
            }

            if (!is_array($conversationData)) {
                continue;
            }

            $conversationId = $conversationData['id'] ?? $conversationData['conversationId'] ?? null;
            if (!$conversationId) {
                continue;
            }

            $conversation = self::upsertConversation($conversationData, $location, (string) $conversationId);
            $messages = self::conversationRequest($location, "https://services.leadconnectorhq.com/conversations/{$conversationId}/messages", [
                'limit' => 100,
            ]);

            foreach (($messages['messages'] ?? $messages['data'] ?? []) as $messageData) {
                if (!is_array($messageData)) {
                    continue;
                }

                if (self::persistConversationMessage($messageData, $location, $conversation)) {
                    $syncedMessages++;
                }
            }
        }

        return $syncedMessages;
    }

    public static function persistConversationMessage(array $payload, ?GhlLocation $location = null, ?Conversation $conversation = null): ?ConversationMessage
    {
        $message = is_array($payload['message'] ?? null) ? $payload['message'] : $payload;
        $conversationId = $payload['conversationId'] ?? $message['conversationId'] ?? $conversation?->platform_conversation_id;
        $contactId = $payload['contactId'] ?? $message['contactId'] ?? null;
        $conversationId ??= $contactId ? "conv_{$contactId}" : null;
        if (!$conversationId) {
            return null;
        }

        $conversation ??= self::upsertConversation($payload, $location, (string) $conversationId);
        $messageId = $payload['messageId'] ?? $message['id'] ?? $payload['id'] ?? null;
        $body = $payload['body'] ?? $message['body'] ?? $payload['text'] ?? $message['text'] ?? null;
        $dateAdded = $payload['dateAdded'] ?? $message['dateAdded'] ?? null;
        $data = [
            'direction' => strtolower((string) ($payload['direction'] ?? $message['direction'] ?? 'inbound')) === 'outbound' ? 'outbound' : 'inbound',
            'message_type' => strtolower((string) ($payload['messageType'] ?? $message['messageType'] ?? $message['type'] ?? 'text')),
            'status' => $payload['status'] ?? $message['status'] ?? null,
            'content_type' => $payload['contentType'] ?? $message['contentType'] ?? null,
            'source' => $payload['source'] ?? $message['source'] ?? null,
            'attachments' => $payload['attachments'] ?? $message['attachments'] ?? [],
            'body' => is_scalar($body) ? (string) $body : ($body ? json_encode($body) : null),
            'raw_payload' => $payload,
            'sent_at' => $dateAdded,
        ];

        $storedMessage = $messageId
            ? $conversation->messages()->updateOrCreate(['platform_message_id' => (string) $messageId], $data)
            : $conversation->messages()->create($data);

        $conversation->update(['last_message' => $data['body'], 'last_message_at' => $dateAdded ?: now()]);
        return $storedMessage;
    }

    private static function upsertConversation(array $payload, ?GhlLocation $location, string $conversationId): Conversation
    {
        $contact = is_array($payload['contact'] ?? null) ? $payload['contact'] : [];
        $contactId = $payload['contactId'] ?? $contact['id'] ?? $contact['_id'] ?? 'unknown';
        $contactName = $payload['contactName'] ?? $contact['name'] ?? trim(($contact['firstName'] ?? '') . ' ' . ($contact['lastName'] ?? ''));

        return Conversation::updateOrCreate(
            ['platform_conversation_id' => $conversationId],
            [
                'contact_id' => (string) $contactId,
                'contact_name' => $contactName ?: null,
                'contact_phone_or_email' => $contact['phone'] ?? $contact['email'] ?? $payload['from'] ?? null,
                'location_id' => $location?->ghl_location_id ?? $payload['locationId'] ?? null,
            ]
        );
    }

    private static function conversationRequest(GhlLocation $location, string $url, array $query = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $location->access_token,
            'Version' => '2021-04-15',
            'Accept' => 'application/json',
        ])->get($url, $query);

        if (!$response->successful()) {
            throw new \RuntimeException('GHL conversations sync failed: ' . $response->body());
        }

        return $response->json() ?? [];
    }

    public static function syncLocationDetails(GhlLocation $location): void
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

    public static function upsertAppointmentsFromWebhookPayload(array $payload, GhlLocation $location): int
    {
        $items = [];

        if (isset($payload['appointment']) && is_array($payload['appointment'])) {
            $items[] = $payload['appointment'];
        }

        if (isset($payload['appointments']) && is_array($payload['appointments'])) {
            $items = array_merge($items, $payload['appointments']);
        }

        if (isset($payload['data']['appointments']) && is_array($payload['data']['appointments'])) {
            $items = array_merge($items, $payload['data']['appointments']);
        }

        if (empty($items) && isset($payload['id']) && is_string($payload['id'])) {
            $items[] = $payload;
        }

        $savedCount = 0;

        foreach ($items as $appointmentData) {
            if (!is_array($appointmentData)) {
                continue;
            }

            if (self::upsertAppointmentFromWebhook($appointmentData, $location)) {
                $savedCount++;
            }
        }

        return $savedCount;
    }

    public static function upsertContactFromWebhook(array $contactData, GhlLocation $location): bool
    {
        $contactId = $contactData['id'] ?? $contactData['_id'] ?? $contactData['contactId'] ?? null;

        if (empty($contactId)) {
            return false;
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
                'company' => $contactData['companyName'] ?? $contactData['company'] ?? null,
                'assigned_to_ghl_user' => $contactData['assignedTo'] ?? null,
                'source' => $contactData['source'] ?? null,
                'tags' => $contactData['tags'] ?? [],
                'custom_fields' => $contactData['customFields'] ?? [],
                'ghl_company_id' => $location->ghl_company_id,
                'date_added' => $contactData['dateAdded'] ?? $contactData['date_added'] ?? null,
                'date_updated' => $contactData['dateUpdated'] ?? $contactData['date_updated'] ?? now(),
            ]
        );

        return true;
    }

    public static function upsertOpportunityFromWebhook(array $opportunityData, GhlLocation $location): bool
    {
        $opportunityId = $opportunityData['id'] ?? $opportunityData['_id'] ?? $opportunityData['opportunityId'] ?? null;

        if (empty($opportunityId)) {
            return false;
        }

        GhlOpportunity::updateOrCreate(
            [
                'ghl_opportunity_id' => (string) $opportunityId,
                'ghl_location_id' => (string) $location->ghl_location_id,
            ],
            [
                'user_id' => $location->user_id,
                'ghl_pipeline_id' => $opportunityData['pipelineId'] ?? null,
                'ghl_pipeline_stage_id' => $opportunityData['pipelineStageId'] ?? $opportunityData['stageId'] ?? null,
                'ghl_contact_id' => $opportunityData['contactId'] ?? null,
                'assigned_to_ghl_user' => $opportunityData['assignedTo'] ?? null,
                'name' => $opportunityData['name'] ?? 'Unnamed Opportunity',
                'status' => $opportunityData['status'] ?? 'open',
                'monetary_value' => $opportunityData['monetaryValue'] ?? 0,
                'source' => $opportunityData['source'] ?? null,
                'date_added' => $opportunityData['dateAdded'] ?? $opportunityData['date_added'] ?? now(),
            ]
        );

        return true;
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
            Log::warning('GHL calendars sync skipped', [
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

        $startTime = now()->subYear()->timestamp * 1000;
        $endTime = now()->addYear()->timestamp * 1000;

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

            $pageToken = null;
            $previousToken = null;

            do {
                $fetchParams = [
                    'locationId' => $location->ghl_location_id,
                    'startTime' => $startTime,
                    'endTime' => $endTime,
                    'limit' => 100,
                ];

                if (!empty($pageToken)) {
                    $fetchParams['pageToken'] = $pageToken;
                }

                $eventsResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $location->access_token,
                    'Version' => '2021-07-28',
                    'Accept' => 'application/json',
                ])->get("https://services.leadconnectorhq.com/calendars/{$calendarId}/events", $fetchParams);

                if (!$eventsResponse->successful()) {
                    Log::error('Failed fetching events for calendar: ' . $calendarId, [
                        'status' => $eventsResponse->status(),
                        'body' => $eventsResponse->body(),
                    ]);
                    break;
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
                        ?? ($eventData['contact']['id'] ?? $eventData['contact']['_id'] ?? null);

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

                $previousToken = $pageToken;
                $pageToken = $eventsResponse->json('nextPageToken')
                    ?? $eventsResponse->json('next_page_token')
                    ?? $eventsResponse->json('pageToken')
                    ?? null;

            } while (!empty($pageToken) && $pageToken !== $previousToken);
        }

        return $savedCount;
    }
}