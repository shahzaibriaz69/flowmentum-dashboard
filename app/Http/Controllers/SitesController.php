<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SitesController extends Controller
{
    public function index()
    {
        $locationId = auth()->user()->location_id;
        return view('sites', compact('locationId'));
    }
}