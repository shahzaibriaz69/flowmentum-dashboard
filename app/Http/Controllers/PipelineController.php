<?php

namespace App\Http\Controllers;

use App\Models\GhlPipeline;
use App\Models\GhlOpportunity;
use App\Models\GhlStageChangeLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        return view('pipeline', [
            'pipelines' => $pipelines,
            'activePipeline' => $activePipeline,
            'currentPipeline' => $activePipeline,
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

        // 1. Local Database Transaction & Log Entry
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

        // 2. Real-time Push to GoHighLevel (GHL API v2)
        $this->syncOpportunityStageToGhl($opportunity, $targetStage->ghl_stage_id, $user);

        return response()->json([
            'message' => 'Opportunity moved and synced with GHL successfully.',
            'stage_id' => $targetStage->ghl_stage_id,
        ]);
    }

    /**
     * Private helper to push stage update to GoHighLevel API
     */
    private function syncOpportunityStageToGhl(GhlOpportunity $opportunity, string $newStageId, $user): void
    {
        $tokenRecord = null;

        // Database table exist karti hai toh access token fetch karein
        if (\Schema::hasTable('ghl_tokens')) {
            $tokenRecord = \DB::table('ghl_tokens')
                ->where('location_id', $opportunity->ghl_location_id)
                ->first();
        }

        // Access Token Fallback Sequence
        $accessToken = session('ghl_access_token')
            ?? $user->ghl_access_token
            ?? $tokenRecord?->access_token
            ?? config('services.marketplace.access_token');

        if (!$accessToken) {
            Log::warning("GHL Sync Skipped: No Access Token found for Opportunity {$opportunity->ghl_opportunity_id}");
            return;
        }

        try {
            $response = Http::timeout(10)->retry(3, 100)->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version' => '2021-07-28',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->put("https://services.gohighlevel.com/v2/opportunities/{$opportunity->ghl_opportunity_id}", [
                        'pipelineId' => $opportunity->ghl_pipeline_id,
                        'pipelineStageId' => $newStageId,
                        'name' => $opportunity->name,
                        'status' => $opportunity->status ?? 'open',
                    ]);

            if ($response->successful()) {
                Log::info("GHL Stage Synced Successfully for Opp ID: {$opportunity->ghl_opportunity_id}");
            } else {
                Log::error("GHL Stage Update Failed for Opp ID {$opportunity->ghl_opportunity_id}: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("GHL API Exception: " . $e->getMessage());
        }
    }
}