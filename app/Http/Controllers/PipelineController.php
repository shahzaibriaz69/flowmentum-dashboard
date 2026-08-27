<?php

namespace App\Http\Controllers;

use App\Models\GhlPipeline;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $locationId = session('active_location_id') 
            ?? $user->ghl_location_id 
            ?? $user->location_id 
            ?? null;

        $pipelines = GhlPipeline::when($locationId, function ($q) use ($locationId) {
            return $q->where('ghl_location_id', $locationId);
        })->get();

        if ($pipelines->isEmpty()) {
            $pipelines = GhlPipeline::all();
        }

        $selectedPipelineId = $request->query('pipeline_id') 
            ?? $pipelines->first()?->ghl_pipeline_id;

        $activePipeline = null;

        if ($selectedPipelineId) {
            $activePipeline = GhlPipeline::where('ghl_pipeline_id', $selectedPipelineId)
                ->with([
                    'stages' => function ($q) {
                        $q->orderBy('position', 'asc');
                    },
                    'stages.opportunities' => function ($q) {
                        $q->latest();
                    },
                    'stages.opportunities.contact'
                ])
                ->first();
        }

        // View name updated to 'pipeline' to match your resources/views/pipeline.blade.php
        return view('pipeline', [
            'pipelines'          => $pipelines,
            'activePipeline'     => $activePipeline,
            'currentPipeline'    => $activePipeline,
            'selectedPipelineId' => $selectedPipelineId,
        ]);
    }
}