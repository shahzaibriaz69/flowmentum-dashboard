<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// ===== GHL Webhook Routes (accessible at /api/ghl-webhooks/...) =====
// 1. Contacts Webhook Route
Route::post("/ghl-webhooks/contacts", function (Request $request) {
    $data = $request->all();
    $type = $data['type'] ?? null;

    $allowedEvents = [
        'ContactCreate',
        'ContactUpdate',
        'ContactDelete',
        'ContactDndUpdate',
        'ContactTagUpdate',
    ];

    if ($type && in_array($type, $allowedEvents)) {
        $location = $data['locationId'] ?? ($data['location']['id'] ?? null);

        if ($location) {
            $foundLocation = \App\Models\GhlLocation::where('ghl_location_id', (string) $location)->first();

            if ($foundLocation) {
                \App\Services\SyncLocationDetailsService::upsertContactFromWebhook($data['contact'] ?? $data, $foundLocation);
            }
        }

        Log::info("GHL Contact Webhook [{$type}]", [
            'type' => $type,
            'payload' => $data,
        ]);
    }

    return response()->json(['status' => 'success'], 200);
});

// 2. Opportunities Webhook Route
Route::post("/ghl-webhooks/opportunities", function (Request $request) {
    $data = $request->all();
    $type = $data['type'] ?? null;

    $allowedEvents = [
        'OpportunityCreate',
        'OpportunityUpdate',
        'OpportunityDelete',
        'OpportunityStatusUpdate',
        'OpportunityAssignedToUpdate',
        'OpportunityMonetaryValueUpdate',
        'OpportunityStageUpdate',
    ];

    if ($type && in_array($type, $allowedEvents)) {
        $location = $data['locationId'] ?? ($data['location']['id'] ?? null);

        if ($location) {
            $foundLocation = \App\Models\GhlLocation::where('ghl_location_id', (string) $location)->first();

            if ($foundLocation) {
                \App\Services\SyncLocationDetailsService::upsertOpportunityFromWebhook($data['opportunity'] ?? $data, $foundLocation);
            }
        }

        Log::info("GHL Opportunity Webhook [{$type}]", [
            'type' => $type,
            'payload' => $data,
        ]);
    }

    return response()->json(['status' => 'success'], 200);
});

// 3. Appointments Webhook Route
Route::post("/ghl-webhooks/appointments", function (Request $request) {
    $data = $request->all();
    $type = $data['type'] ?? null;

    $allowedEvents = [
        'AppointmentCreate',
        'AppointmentDelete',
        'AppointmentUpdate',
    ];

    if (!$type || !in_array($type, $allowedEvents)) {
        return response()->json(['status' => 'ignored'], 200);
    }

    $locationId = $data['locationId'] ?? ($data['location']['id'] ?? ($data['appointment']['locationId'] ?? null));

    if ($locationId) {
        $foundLocation = \App\Models\GhlLocation::where('ghl_location_id', (string) $locationId)->first();

        if ($foundLocation) {
            \App\Services\SyncLocationDetailsService::upsertAppointmentsFromWebhookPayload($data, $foundLocation);

            Log::info('GHL Appointment Webhook persisted', [
                'type' => $type,
                'payload_keys' => array_keys($data),
            ]);
        }
    }

    return response()->json(['status' => 'success'], 200);
});
// ===== End GHL Webhook Routes =====