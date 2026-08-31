<?php

namespace App\Services;

use App\Models\GhlContact;
use App\Models\GhlCustomField;
use App\Models\GhlLocation;
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

            foreach ($contactData['tags'] ?? [] as $tagData) {
                $tagId = $tagData['id'] ?? $tagData['_id'] ?? $tagData['tagId'] ?? null;
                $tagName = $tagData['name'] ?? $tagData['tagName'] ?? null;

                if (empty($tagId) && empty($tagName)) {
                    continue;
                }

                $normalizedTagId = (string) ($tagId ?? $tagName);

                GhlTag::updateOrCreate(
                    [
                        'ghl_location_id' => (string) $location->ghl_location_id,
                        'ghl_tag_id' => $normalizedTagId,
                    ],
                    [
                        'name' => $tagName ?? $normalizedTagId,
                    ]
                );

                DB::table('ghl_contact_tags')->updateOrInsert(
                    [
                        'ghl_location_id' => (string) $location->ghl_location_id,
                        'ghl_contact_id' => (string) $contactId,
                        'ghl_tag_id' => $normalizedTagId,
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
}