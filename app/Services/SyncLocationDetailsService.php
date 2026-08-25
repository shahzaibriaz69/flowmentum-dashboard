<?php

namespace App\Services;

use App\Models\GhlLocation;
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

    public static function syncUsers(GhlLocation $location)
    {
        
        $ghlResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $location->access_token,
            'Version' => '2021-07-28',
        ])->get("https://services.leadconnectorhq.com/users/?locationId={$location->ghl_location_id}");

        return $ghlResponse->json();
        
    }
}