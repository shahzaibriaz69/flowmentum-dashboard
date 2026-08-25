<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PeopleController extends Controller
{
    public function index()
    {
        $locationId = auth()->user()->location_id;
        return view('people', compact('locationId'));
    }
}