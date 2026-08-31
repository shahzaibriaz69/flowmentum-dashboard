<?php

use App\Models\GhlLocation;
use App\Models\User;
use App\Services\SyncLocationDetailsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

it('syncs contact tags and contact custom fields into the related tables', function () {
    $user = User::factory()->create();

    $location = GhlLocation::create([
        'user_id' => $user->id,
        'ghl_location_id' => 'loc_123',
        'name' => 'Demo Location',
        'access_token' => 'token-123',
        'refresh_token' => 'refresh-123',
        'expires_at' => now()->addDay(),
    ]);

    Http::fake([
        'https://services.leadconnectorhq.com/contacts/*' => Http::response([
            'contacts' => [[
                'id' => 'contact_1',
                'firstName' => 'John',
                'lastName' => 'Doe',
                'tags' => [
                    ['id' => 'tag_1', 'name' => 'vip'],
                    ['name' => 'marketing'],
                ],
                'customFields' => [
                    ['id' => 'field_1', 'value' => 'Acme'],
                    ['fieldId' => 'field_2', 'value' => 'North'],
                ],
            ]],
        ], 200),
    ]);

    $count = SyncLocationDetailsService::syncContacts($location);

    expect($count)->toBe(1)
        ->and(DB::table('ghl_contact_tags')->where('ghl_contact_id', 'contact_1')->count())->toBe(2)
        ->and(DB::table('ghl_contact_custom_fields')->where('ghl_contact_id', 'contact_1')->count())->toBe(2)
        ->and(DB::table('ghl_contact_tags')->where('ghl_contact_id', 'contact_1')->where('ghl_tag_id', 'tag_1')->exists())->toBeTrue();
});
