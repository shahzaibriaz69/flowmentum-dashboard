<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function index()
    {
        $locationId = auth()->user()->location_id;
        return view('pipeline', compact('locationId'));
    }
}