<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function index()
    {
        $locationId = auth()->user()->location_id;
        return view('inbox', compact('locationId'));
    }
}