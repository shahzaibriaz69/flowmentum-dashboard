<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/{page}', function (string $page) {
    abort_unless(in_array($page, ['people', 'inbox', 'pipeline', 'marketing', 'automations', 'sites'], true), 404);

    return view('workspace', compact('page'));
})->where('page', 'people|inbox|pipeline|marketing|automations|sites')->name('workspace');
