<?php

namespace App\Http\Controllers;

use App\Models\GhlPipeline;
use App\Models\GhlLocation;
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

        $locationIds = $user->locationIds ?? [];

        $pipelines = GhlPipeline::when(!empty($locationIds), function ($q) use ($locationIds) {
            return $q->whereIn('ghl_location_id', $locationIds);
        })->get();

        if ($pipelines->isEmpty()) {
            $pipelines = GhlPipeline::all();
        }

        $defaultPipeline = $pipelines->first(function (GhlPipeline $pipeline) {
            $pipelineName = strtolower((string) $pipeline->name);

            return str_contains($pipelineName, 'appoin')
                && str_contains($pipelineName, 'test');
        }) ?? $pipelines->first();

        $selectedPipelineId = $request->query('pipeline_id')
            ?? $defaultPipeline?->ghl_pipeline_id;

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

    public function moveOpportunity(Request $request, $opportunityId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'stage_id' => ['required', 'string'],
            ]);

            $opportunity = GhlOpportunity::where('id', $opportunityId)
                ->orWhere('ghl_opportunity_id', $opportunityId)
                ->first();

            if (!$opportunity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Opportunity record not found.'
                ], 404);
            }

            $user = $request->user();
            $locationId = $request->attributes->get('active_location_id')
                ?? $user->ghl_location_id
                ?? $user->location_id
                ?? null;

            $targetStage = $opportunity->pipeline
                ? $opportunity->pipeline->stages()->where('ghl_stage_id', $validated['stage_id'])->first()
                : null;

            if (!$targetStage) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected stage does not belong to this pipeline.'
                ], 422);
            }

            if ($opportunity->ghl_pipeline_stage_id === $targetStage->ghl_stage_id) {
                return response()->json([
                    'success' => true,
                    'message' => 'Opportunity is already in this stage.'
                ]);
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

           
            $ghlSynced = $this->syncOpportunityStageToGhl($opportunity, $targetStage->ghl_stage_id);

            return response()->json([
                'success'    => true,
                'message'    => $ghlSynced 
                    ? 'Opportunity moved locally and synced with GHL.' 
                    : 'Opportunity moved locally, but GHL sync failed.',
                'ghl_synced' => $ghlSynced,
                'stage_id'   => $targetStage->ghl_stage_id,
            ]);

        } catch (\Exception $e) {
            Log::error("Move Opportunity Exception: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

   
    private function syncOpportunityStageToGhl(GhlOpportunity $opportunity, string $newStageId): bool
    {
        // 1. Location-specific token fetching
        $location = GhlLocation::where('ghl_location_id', $opportunity->ghl_location_id)->first();
        $accessToken = $this->getValidAccessToken($location);

        if (blank($accessToken)) {
            Log::warning("GHL Sync Skipped: No Access Token found for Opportunity {$opportunity->ghl_opportunity_id}");
            return false;
        }

        try {
            $url = "https://services.leadconnectorhq.com/opportunities/{$opportunity->ghl_opportunity_id}";

            $payload = [
                'pipelineId'      => $opportunity->ghl_pipeline_id,
                'pipelineStageId' => $newStageId,
                'name'            => $opportunity->name,
                'status'          => $opportunity->status ?? 'open',
            ];

            $response = $this->sendGhlOpportunityUpdate($url, $payload, $accessToken);

            if ($response->status() === 401 && $location && $this->refreshGhlToken($location)) {
                $response = $this->sendGhlOpportunityUpdate($url, $payload, $location->access_token);
            }

            if ($response->successful()) {
                Log::info("GHL Stage Synced Successfully for Opp ID: {$opportunity->ghl_opportunity_id}");
                return true;
            }

            
            Log::error("GHL Stage Update Failed [{$response->status()}] for Opp ID: {$opportunity->ghl_opportunity_id}", [
                'url'           => $url,
                'response_body' => $response->json() ?? $response->body(),
                'location_id'   => $opportunity->ghl_location_id,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error("GHL API Exception: " . $e->getMessage(), [
                'opportunity_id' => $opportunity->ghl_opportunity_id,
                'target_stage'   => $newStageId,
            ]);
            return false;
        }
    }

    private function sendGhlOpportunityUpdate(string $url, array $payload, string $accessToken)
    {
        return Http::timeout(10)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Version'       => 'v3',
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->put($url, $payload);
    }

    private function getValidAccessToken(?GhlLocation $location): ?string
    {
        if (!$location) {
            return config('services.marketplace.access_token');
        }

        if ($location->expires_at?->isPast() && !$this->refreshGhlToken($location)) {
            return null;
        }

        return $location->access_token;
    }

    private function refreshGhlToken(GhlLocation $location): bool
    {
        if (blank($location->refresh_token)) {
            return false;
        }

        $response = Http::asForm()->post(config('services.marketplace.token_url'), [
            'grant_type'    => 'refresh_token',
            'client_id'     => config('services.marketplace.client_id'),
            'client_secret' => config('services.marketplace.client_secret'),
            'refresh_token' => $location->refresh_token,
        ]);

        if (!$response->successful() || blank($response->json('access_token'))) {
            Log::error('GHL Token Refresh Failed: ' . $response->body());
            return false;
        }

        $location->update([
            'access_token'  => $response->json('access_token'),
            'refresh_token' => $response->json('refresh_token', $location->refresh_token),
            'expires_at'    => now()->addSeconds((int) $response->json('expires_in', 86400)),
        ]);

        return true;
    }
}