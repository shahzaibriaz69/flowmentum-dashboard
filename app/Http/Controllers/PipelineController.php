<?php

namespace App\Http\Controllers;

use App\Models\GhlPipeline;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function index(Request $request)
    {
        // 1. Database se sab pipelines fetch karein (Debugging ke liye direct fetch)
        $pipelines = GhlPipeline::all();

        // 2. Selected Pipeline ID handle karein
        $selectedPipelineId = $request->query('pipeline_id', $pipelines->first()?->ghl_pipeline_id);

        $activePipeline = null;
        if ($selectedPipelineId) {
            $activePipeline = GhlPipeline::where('ghl_pipeline_id', $selectedPipelineId)
                ->with(['stages' => function ($query) {
                    $query->orderBy('position', 'asc')->with('opportunities');
                }])
                ->first();
        }

        return view('pipeline', compact('pipelines', 'activePipeline', 'selectedPipelineId'));
    }
}