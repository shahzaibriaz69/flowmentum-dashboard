<?php

namespace App\Http\Controllers;

use App\Models\GhlPipeline;
use App\Models\GhlOpportunity;
use App\Models\GhlStageChangeLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function moveOpportunity(Request $request, GhlOpportunity $opportunity): JsonResponse
    {
        $validated = $request->validate([
            'stage_id' => ['required', 'string'],
        ]);

        $user = $request->user();
        $locationId = session('active_location_id')
            ?? $user->ghl_location_id
            ?? $user->location_id
            ?? null;

        abort_unless(
            !$locationId || $opportunity->ghl_location_id === $locationId,
            404
        );

        $targetStage = $opportunity->pipeline
            ? $opportunity->pipeline->stages()->where('ghl_stage_id', $validated['stage_id'])->first()
            : null;

        abort_unless($targetStage, 422, 'The selected stage does not belong to this pipeline.');

        if ($opportunity->ghl_pipeline_stage_id === $targetStage->ghl_stage_id) {
            return response()->json(['message' => 'Opportunity is already in this stage.']);
        }

        $fromStageId = $opportunity->ghl_pipeline_stage_id;

        DB::transaction(function () use ($opportunity, $targetStage, $fromStageId, $locationId): void {
            $opportunity->update([
                'ghl_pipeline_stage_id' => $targetStage->ghl_stage_id,
            ]);

            GhlStageChangeLog::create([
                'ghl_location_id' => $locationId ?? $opportunity->ghl_location_id,
                'ghl_opportunity_stage_id' => $opportunity->ghl_opportunity_id,
                'ghl_pipeline_id' => $opportunity->ghl_pipeline_id,
                'from_stage_id' => $fromStageId,
                'to_stage_id' => $targetStage->ghl_stage_id,
                'changed_at' => now(),
            ]);
        });

        return response()->json([
            'message' => 'Opportunity moved successfully.',
            'stage_id' => $targetStage->ghl_stage_id,
        ]);
    }
}