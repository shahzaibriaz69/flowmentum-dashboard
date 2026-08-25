<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AutomationsController extends Controller
{
    public function index()
    {
        $locationId = auth()->user()->location_id;
        return view('automations', compact('locationId'));
    }
}