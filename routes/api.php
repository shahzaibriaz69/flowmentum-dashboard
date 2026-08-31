<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// 1. Contacts Webhook Route
Route::post("/ghl-webhooks/contacts", function (Request $request) {
    $data = $request->all();
    $type = $data['type'] ?? null;

    $allowedEvents = [
        'ContactCreate',
        'ContactUpdate',
        'ContactDelete',
        'ContactDndUpdate',
        'ContactTagUpdate'
    ];

    if ($type && in_array($type, $allowedEvents)) {
        Log::info("GHL Contact Webhook [{$type}]", [
            'type' => $type,
            'payload' => $data
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
        'OpportunityStageUpdate'
    ];

    if ($type && in_array($type, $allowedEvents)) {
        Log::info("GHL Opportunity Webhook [{$type}]", [
            'type' => $type,
            'payload' => $data
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
        'AppointmentUpdate'
    ];

    if ($type && in_array($type, $allowedEvents)) {
        $location = null;

        if (!empty($data['locationId'] ?? null)) {
            $location = \App\Models\GhlLocation::where('ghl_location_id', (string) $data['locationId'])->first();
        }

        if (!$location && !empty($data['location']['id'] ?? null)) {
            $location = \App\Models\GhlLocation::where('ghl_location_id', (string) $data['location']['id'])->first();
        }

        if (!$location && !empty($data['appointment']['locationId'] ?? null)) {
            $location = \App\Models\GhlLocation::where('ghl_location_id', (string) $data['appointment']['locationId'])->first();
        }

        if ($location) {
            \App\Services\SyncLocationDetailsService::upsertAppointmentFromWebhook($data['appointment'] ?? $data, $location);
        } else {
            Log::warning('GHL Appointment Webhook received without matching location', [
                'type' => $type,
                'payload' => $data,
            ]);
        }
    }

    Log::info("GHL Appointment Webhook [{$type}]", [
        'type' => $type,
        'payload' => $data
    ]);

    return response()->json(['status' => 'success'], 200);
});