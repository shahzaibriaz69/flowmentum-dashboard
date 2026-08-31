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
                'tags' => ['vip', 'marketing'],
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
        ->and(DB::table('ghl_contact_tags')->where('ghl_contact_id', 'contact_1')->whereIn('ghl_tag_id', ['vip', 'marketing'])->count())->toBe(2);
});

it('stores new contact opportunities and appointments in the database', function () {
    $user = User::factory()->create();

    $location = GhlLocation::create([
        'user_id' => $user->id,
        'ghl_location_id' => 'loc_789',
        'ghl_company_id' => 'company_123',
        'name' => 'Demo Location',
        'access_token' => 'token-456',
        'refresh_token' => 'refresh-456',
        'expires_at' => now()->addDay(),
    ]);

    Http::fake([
        '*https://services.leadconnectorhq.com/opportunities/search*' => Http::response([
            'opportunities' => [[
                'id' => 'opp_1',
                'pipelineId' => 'pipeline_1',
                'pipelineStageId' => 'stage_1',
                'contactId' => 'contact_2',
                'name' => 'New deal',
                'status' => 'open',
                'monetaryValue' => 2500,
                'assignedTo' => 'user_1',
                'source' => 'Website',
            ]],
        ], 200),
        '*https://services.leadconnectorhq.com/calendars/calendar_1/events*' => Http::response([
            'events' => [[
                'id' => 'appointment_1',
                'contactId' => 'contact_2',
                'calendarId' => 'calendar_1',
                'title' => 'Discovery Call',
                'status' => 'scheduled',
                'startTime' => '2026-08-31T10:00:00Z',
                'endTime' => '2026-08-31T11:00:00Z',
                'notes' => 'Follow up',
                'source' => 'Website',
                'users' => ['user_1'],
            ]],
        ], 200),
        '*https://services.leadconnectorhq.com/calendars*' => Http::response([
            'calendars' => [[
                'id' => 'calendar_1',
                'name' => 'Sales Calendar',
            ]],
        ], 200),
    ]);

    $opportunityCount = SyncLocationDetailsService::syncOpportunities($location);
    $appointmentCount = SyncLocationDetailsService::syncAppointments($location);

    expect($opportunityCount)->toBe(1)
        ->and($appointmentCount)->toBe(1)
        ->and(DB::table('ghl_opportunities')->where('ghl_opportunity_id', 'opp_1')->exists())->toBeTrue()
        ->and(DB::table('ghl_appointments')->where('ghl_appointment_id', 'appointment_1')->exists())->toBeTrue();
});

it('does not crash when a tag already exists for the location and when calendars are unavailable', function () {
    $user = User::factory()->create();
    $location = GhlLocation::create([
        'user_id' => $user->id,
        'ghl_location_id' => 'loc_456',
        'name' => 'Demo Location',
        'access_token' => 'token-789',
        'refresh_token' => 'refresh-789',
        'expires_at' => now()->addDay(),
    ]);

    DB::table('ghl_tags')->insert([
        'ghl_location_id' => 'loc_456',
        'ghl_tag_id' => 'ahsan',
        'name' => 'ahsan',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Http::fake([
        'https://services.leadconnectorhq.com/contacts/*' => Http::response([
            'contacts' => [[
                'id' => 'contact_99',
                'firstName' => 'Ahsan',
                'lastName' => 'Ali',
                'tags' => ['ahsan', 'vip'],
                'customFields' => [],
            ]],
        ], 200),
        'https://services.leadconnectorhq.com/calendars*' => Http::response([], 401),
    ]);

    $count = SyncLocationDetailsService::syncContacts($location);
    $appointments = SyncLocationDetailsService::syncAppointments($location);

    expect($count)->toBe(1)
        ->and($appointments)->toBe(0)
        ->and(DB::table('ghl_contact_tags')->where('ghl_contact_id', 'contact_99')->count())->toBe(2);
});

it('stores appointments when GHL returns them under appointments and with nested contact ids', function () {
    $user = User::factory()->create();
    $location = GhlLocation::create([
        'user_id' => $user->id,
        'ghl_location_id' => 'loc_910',
        'ghl_company_id' => 'company_910',
        'name' => 'Appointment Location',
        'access_token' => 'token-910',
        'refresh_token' => 'refresh-910',
        'expires_at' => now()->addDay(),
    ]);

    Http::fake(function ($request) {
        $url = $request->url();

        if (str_contains($url, '/calendars?')) {
            return Http::response([
                'calendars' => [[
                    'id' => 'calendar_2',
                    'name' => 'Sales Calendar',
                ]],
            ], 200);
        }

        if (str_contains($url, '/calendars/calendar_2/events')) {
            return Http::response([
                'appointments' => [[
                    'id' => 'appointment_2',
                    'contact' => ['id' => 'contact_77'],
                    'name' => 'Demo Call',
                    'status' => 'scheduled',
                    'startDateTime' => '2026-08-31T09:00:00Z',
                    'endDateTime' => '2026-08-31T09:30:00Z',
                    'notes' => 'Follow-up',
                ]],
            ], 200);
        }

        return Http::response([], 404);
    });

    $count = SyncLocationDetailsService::syncAppointments($location);

    expect($count)->toBe(1)
        ->and(DB::table('ghl_appointments')->where('ghl_appointment_id', 'appointment_2')->exists())->toBeTrue()
        ->and(DB::table('ghl_appointments')->where('ghl_appointment_id', 'appointment_2')->value('ghl_contact_id'))->toBe('contact_77');
});

it('persists a new or updated GHL appointment webhook payload to the database', function () {
    $user = User::factory()->create();
    $location = GhlLocation::create([
        'user_id' => $user->id,
        'ghl_location_id' => 'loc_111',
        'ghl_company_id' => 'company_111',
        'name' => 'Webhook Location',
        'access_token' => 'token-111',
        'refresh_token' => 'refresh-111',
        'expires_at' => now()->addDay(),
    ]);

    $first = SyncLocationDetailsService::upsertAppointmentFromWebhook([
        'id' => 'webhook_apt_1',
        'contact' => ['id' => 'contact_abc'],
        'title' => 'Initial Booking',
        'status' => 'scheduled',
        'startDateTime' => '2026-08-31T10:00:00Z',
        'endDateTime' => '2026-08-31T10:30:00Z',
    ], $location);

    $updated = SyncLocationDetailsService::upsertAppointmentFromWebhook([
        'id' => 'webhook_apt_1',
        'contact' => ['id' => 'contact_abc'],
        'title' => 'Updated Booking',
        'status' => 'completed',
        'startDateTime' => '2026-08-31T11:00:00Z',
        'endDateTime' => '2026-08-31T11:30:00Z',
    ], $location);

    expect($first)->toBeTrue()
        ->and($updated)->toBeTrue()
        ->and(DB::table('ghl_appointments')->where('ghl_appointment_id', 'webhook_apt_1')->value('title'))->toBe('Updated Booking')
        ->and(DB::table('ghl_appointments')->where('ghl_appointment_id', 'webhook_apt_1')->value('ghl_contact_id'))->toBe('contact_abc');
});
