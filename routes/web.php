<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/people', fn () => view('workspace', ['page' => 'people']))->name('people');
Route::get('/inbox', fn () => view('workspace', ['page' => 'inbox']))->name('inbox');
Route::get('/pipeline', fn () => view('workspace', ['page' => 'pipeline']))->name('pipeline');
Route::get('/marketing', fn () => view('workspace', ['page' => 'marketing']))->name('marketing');
Route::get('/automations', fn () => view('workspace', ['page' => 'automations']))->name('automations');
Route::get('/sites', fn () => view('workspace', ['page' => 'sites']))->name('sites');

// Keeps the existing shared navigation links backward-compatible.
Route::get('/workspace/{page}', function (string $page) {
    abort_unless(in_array($page, ['people', 'inbox', 'pipeline', 'marketing', 'automations', 'sites'], true), 404);

    return redirect()->route($page);
})->where('page', 'people|inbox|pipeline|marketing|automations|sites')->name('workspace');
