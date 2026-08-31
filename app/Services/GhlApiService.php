<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GhlApiService
{
    protected string $baseUrl = 'https://services.gohighlevel.com/v2/';

    /**
     * Common Header for GHL API v2
     */
    protected function headers(): array
    {
        $accessToken = auth()->user()->ghl_access_token ?? session('ghl_access_token');

        return [
            'Authorization' => 'Bearer ' . $accessToken,
            'Version' => '2021-07-28',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * 1. Pipeline Create / Update in GHL
     */
    public function syncPipeline(array $data, ?string $ghlPipelineId = null): ?array
    {
        try {
            if ($ghlPipelineId) {
                // Update Pipeline
                $response = Http::withHeaders($this->headers())
                    ->put($this->baseUrl . "pipelines/{$ghlPipelineId}", $data);
            } else {
                // Create Pipeline
                $response = Http::withHeaders($this->headers())
                    ->post($this->baseUrl . 'pipelines/', $data);
            }

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('GHL Pipeline Sync Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 2. Opportunity Stage & Status Move/Update in GHL
     */
    public function updateOpportunity(string $ghlOpportunityId, array $payload): bool
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->put($this->baseUrl . "opportunities/{$ghlOpportunityId}", $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('GHL Opportunity Update Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 3. Create Opportunity in GHL
     */
    public function createOpportunity(array $payload): ?array
    {
        try {
            $response = Http::withHeaders($this->headers())
                ->post($this->baseUrl . 'opportunities/', $payload);

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('GHL Opportunity Create Error: ' . $e->getMessage());
            return null;
        }
    }
}