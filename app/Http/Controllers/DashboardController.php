<?php

namespace App\Http\Controllers;

use App\Models\GhlContact;
use App\Models\GhlOpportunity;
use App\Models\GhlPipeline;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function people()
    {
        // GhlContact model se users fetch karein
        $people = GhlContact::latest()->get();

        return view('workspace', [
            'page' => 'people',
            'people' => $people
        ]);
    }

    public function pipeline()
    {
        // Pipelines aur Opportunities data fetch karein
        $pipelines = GhlPipeline::with('stages')->get();
        $opportunities = GhlOpportunity::all();

        return view('workspace', [
            'page' => 'pipeline',
            'pipelines' => $pipelines,
            'opportunities' => $opportunities
        ]);
    }

    public function inbox()
    {
        return view('workspace', ['page' => 'inbox']);
    }

    public function marketing()
    {
        return view('workspace', ['page' => 'marketing']);
    }

    public function automations()
    {
        return view('workspace', ['page' => 'automations']);
    }

    public function sites()
    {
        return view('workspace', ['page' => 'sites']);
    }
}