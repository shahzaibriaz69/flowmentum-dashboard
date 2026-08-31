<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\AutomationsController;
use App\Http\Controllers\SitesController;
use App\Http\Controllers\LocationSyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// ===== GHL Webhook Routes (Public, no middleware) =====
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

// 1. Root Route
Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'check.location'])->name('dashboard');

// 2. Explicit Dashboard Route
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'check.location']);

// 3. Protected CRM Routes
Route::middleware(['auth', 'check.location'])->group(function () {
    Route::get('/people', [PeopleController::class, 'index'])->name('people');
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
    // Temporary test route outside middleware
    Route::get('/test-pipeline', [App\Http\Controllers\PipelineController::class, 'index']);
    Route::get('/marketing', [MarketingController::class, 'index'])->name('marketing');
    Route::get('/automations', [AutomationsController::class, 'index'])->name('automations');
    Route::get('/sites', [SitesController::class, 'index'])->name('sites');

    // Sync Location POST route
    Route::post('/location/sync', [LocationSyncController::class, 'sync'])->name('location.sync');
});

// 4. Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// 5. Shared Navigation Legacy Route
Route::get('/workspace/{page}', function (string $page) {
    abort_unless(in_array($page, ['people', 'inbox', 'pipeline', 'marketing', 'automations', 'sites'], true), 404);
    return redirect()->route($page);
})->where('page', 'people|inbox|pipeline|marketing|automations|sites')->name('workspace');

// Auth routes
require __DIR__ . '/auth.php';