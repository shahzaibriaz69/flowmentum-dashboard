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

    if (!$type || in_array($type, $allowedEvents)) {
        Log::error("GHL Appointment Webhook [{$type}]", [
            'type' => $type,
            'payload' => $data
        ]);

        return response()->json(['status' => 'failed'], 400);
    }

    $location = $data['locationId'] ??$data['location']['id']??$data['appointment']['locationId']?? null;

    if ($location) {
        $savedAppointments = \App\Services\SyncLocationDetailsService::upsertAppointmentsFromWebhookPayload($data, $location);

        Log::info('GHL Appointment Webhook persisted', [
            'type' => $type,
            'saved_count' => $savedAppointments,
            'payload_keys' => array_keys($data),
        ]);
    } else {
        Log::error('GHL Appointment Webhook received without matching location.', [
            'type' => $type,
            'payload' => $data,
        ]);

        return response()->json(['status' => 'failed'], 400);
    }


    Log::info("GHL Appointment Webhook [{$type}]", [
        'type' => $type,
        'payload' => $data
    ]);

    return response()->json(['status' => 'success'], 200);
});