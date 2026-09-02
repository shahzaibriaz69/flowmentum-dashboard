<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\PipelineController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('/people', fn() => view('workspace', ['page' => 'people']))->name('people');
    Route::get('/pipeline', [PipelineController::class, 'index'])->name('pipeline');
    Route::patch('/pipeline/opportunities/{opportunity}/stage', [PipelineController::class, 'moveOpportunity'])
        ->name('pipeline.opportunities.move');
    Route::get('/marketing', fn() => view('workspace', ['page' => 'marketing']))->name('marketing');
    Route::get('/automations', fn() => view('workspace', ['page' => 'automations']))->name('automations');
    Route::get('/sites', fn() => view('workspace', ['page' => 'sites']))->name('sites');

    // Keeps the existing shared navigation links backward-compatible.
    Route::get('/workspace/{page}', function (string $page) {
        abort_unless(in_array($page, ['people', 'inbox', 'pipeline', 'marketing', 'automations', 'sites'], true), 404);

        return redirect()->route($page);
    })->where('page', 'people|inbox|pipeline|marketing|automations|sites')->name('workspace');

    Route::controller(MarketplaceController::class)
        ->prefix('authorization/marketplace')
        ->name('marketplace.')
        ->middleware('can:auth location')
        ->group(function () {

            Route::get('/authenticate', 'authenticate')->name('authenticate');
            Route::get('/callback', 'callback')->name('callback');
        });

    Route::get('/authrozation/marketplace/callback', [MarketplaceController::class, 'callback'])
        ->middleware('can:auth location');
});
