<?php

namespace App\Services;

use App\Models\GhlLocation;
use App\Models\GhlUser;
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
}