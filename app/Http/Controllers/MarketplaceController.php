<?php

namespace App\Http\Controllers;

use App\Models\GhlCustomField;
use App\Models\GhlCustomValue;
use App\Models\GhlLocation;
use App\Models\GhlPipeline;
use App\Models\GhlPipelineStage;
use App\Models\GhlTag;
use App\Models\GhlUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MarketplaceController extends Controller
{
    public static function O_AUTH_URL(): string
    {
        return config('services.marketplace.authorize_url') . '?' . http_build_query(static::baseConnectQuery());
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $state = Str::random(40);
        $request->session()->put('marketplace_oauth_state', $state);
        Cache::put('marketplace_oauth_state:' . $state, $request->user()->id, now()->addMinutes(10));

        return redirect()->away(self::O_AUTH_URL());
    }

    private static function baseConnectQuery(): array
    {
        return [
            'response_type' => 'code',
            'redirect_uri' => config('services.marketplace.callback_url'),
            'client_id' => config('services.marketplace.client_id'),
            'state' => session('marketplace_oauth_state'),
            'scope' => 'contacts.readonly contacts.write locations.readonly locations/customValues.readonly locations/customValues.write locations/customFields.readonly locations/customFields.write locations/tasks.readonly locations/tasks.write recurring-tasks.readonly recurring-tasks.write locations/tags.readonly locations/tags.write locations/templates.readonly opportunities.readonly opportunities.write pipelines.readonly pipelines.write pipelines.create calendars.readonly calendars.write calendars/events.readonly calendars/events.write calendars/groups.readonly calendars/groups.write calendars/resources.readonly calendars/resources.write users.readonly users.write workflows.readonly',
        ];
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->filled('error')) {
            $message = $request->string('error_description', 'GoHighLevel authorization was denied.')->toString();
            Log::warning('GoHighLevel OAuth authorization was denied.', [
                'error' => $request->query('error'),
                'error_description' => $message,
            ]);

            return redirect()->route('dashboard')->with('error', $message);
        }

        if (!$request->filled('code')) {
            Log::warning('GoHighLevel OAuth callback received without query parameters.', [
                'url' => $request->fullUrlWithoutQuery(['code', 'state']),
                'query_keys' => array_keys($request->query()),
            ]);

            return redirect()->route('dashboard')->with('error', 'GoHighLevel did not return an authorization code.');
        }

        abort_unless(
            $this->validOAuthState($request),
            419,
            'Invalid OAuth state.'
        );

        $response = Http::asForm()->post(config('services.marketplace.token_url'), [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.marketplace.client_id'),
            'client_secret' => config('services.marketplace.client_secret'),
            'code' => $request->query('code'),
            'redirect_uri' => config('services.marketplace.callback_url'),
        ]);

        abort_unless($response->successful(), 502, 'GoHighLevel token exchange failed: ' . $response->json('error_description', $response->body()));

        $tokens = $response->json();
        $locationId = $tokens['locationId'] ?? $tokens['location_id'] ?? null;
        abort_unless($locationId, 502, 'GoHighLevel did not return a location ID.');

        $location = GhlLocation::updateOrCreate(
            ['ghl_location_id' => $locationId],
            [
                'user_id' => $request->user()->id,
                'ghl_company_id' => $tokens['companyId'] ?? $tokens['company_id'] ?? null,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'expires_at' => now()->addSeconds((int) ($tokens['expires_in'] ?? 86400)),
            ]
        );

        $request->user()->update(['location_id' => $location->ghl_location_id]);

        $this->executeFullSync($location);

        return redirect()->route('dashboard')->with('success', 'GoHighLevel location connected and synced successfully.');
    }

    /**
     * Master Sync Location Trigger (Route: location.sync)
     */
    public function syncLocation(Request $request): RedirectResponse
    {
        $user = $request->user();

        $locationId = $user->locationId;

        $location = GhlLocation::where('ghl_location_id', $locationId)->first();

        if (!$location) {
            return redirect()->back()->with('error', 'No connected GHL location found for this user.');
        }

        $location = $this->getValidAccessToken($location);

        if (!$location) {
            return redirect()->back()->with('error', 'No connected GHL location found for this user.');
        }

        $results = $this->executeFullSync($location);

        return redirect()->back()->with('success', 'Sync Completed! Users: ' . ($results['users'] ? 'OK' : 'Failed') . ', Custom Values: ' . ($results['custom_values'] ? 'OK' : 'Failed') . ', Custom Fields: ' . ($results['custom_fields'] ? 'OK' : 'Failed') . ', Tags: ' . ($results['tags'] ? 'OK' : 'Failed') . ', Pipelines: ' . ($results['pipelines'] ? 'OK' : 'Failed'));
    }

    /**
     * Execute All Sync Services in Sequence
     */
    private function executeFullSync(GhlLocation $location): array
    {
        return [
            'users' => $this->syncUsers($location),
            'custom_values' => $this->syncCustomValues($location),
            'custom_fields' => $this->syncCustomFields($location),
            'tags' => $this->syncTags($location),
            'pipelines' => $this->syncPipelines($location),
        ];
    }

    /**
     * 1. Sync Users
     */
    private function syncUsers(GhlLocation $location): bool
    {
       

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $location->access_token,
            'Version' => '2021-07-28',
            'Accept' => 'application/json',
        ])->get("https://services.leadconnectorhq.com/users/?locationId={$location->ghl_location_id}");

        if ($response->successful()) {
            $responseData = $response->json();
            $users = $responseData['users'] ?? (is_array($responseData) && array_is_list($responseData) ? $responseData : []);

            $saved = 0;
            foreach ($users as $userData) {
                $userId = $userData['id'] ?? $userData['_id'] ?? $userData['userId'] ?? null;
                if (!$userId)
                    continue;

                GhlUser::updateOrCreate(
                    ['ghl_user_id' => (string) $userId],
                    [
                        'user_id' => $location - id,
                        'ghl_location_ids' => $userData['locationIds'] ?? [$location->ghl_location_id],
                        'first_name' => $userData['firstName'] ?? null,
                        'last_name' => $userData['lastName'] ?? null,
                        'email' => $userData['email'] ?? null,
                        'phone' => $userData['phone'] ?? null,
                        'type' => $userData['type'] ?? null,
                        'role' => $userData['role'] ?? null,
                        'permissions' => $userData['permissions'] ?? null,
                    ]
                );
                $saved++;
            }
            return $saved > 0;
        }

        Log::error('GHL Users Sync Failed: ' . $response->body());
        return false;
    }

    /**
     * 2. Sync Custom Values
     */
    private function syncCustomValues(GhlLocation $location): bool
    {

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $location->access_token,
            'Version' => '2021-07-28',
            'Accept' => 'application/json',
        ])->get("https://services.leadconnectorhq.com/locations/{$location->ghl_location_id}/custom-values");

        if ($response->successful()) {
            $responseData = $response->json();
            $customValues = $responseData['customValues'] ?? $responseData['values'] ?? (is_array($responseData) && array_is_list($responseData) ? $responseData : []);

            $saved = 0;
            foreach ($customValues as $item) {
                $valueId = $item['id'] ?? $item['valueId'] ?? $item['_id'] ?? null;
                if (!$valueId)
                    continue;

                GhlCustomValue::updateOrCreate(
                    [
                        'ghl_location_id' => (string) $location->ghl_location_id,
                        'ghl_value_id' => (string) $valueId,
                    ],
                    [
                        'name' => $item['name'] ?? null,
                        'field_key' => $item['fieldKey'] ?? null,
                        'value' => $item['value'] ?? null,
                    ]
                );
                $saved++;
            }
            return $saved > 0;
        }

        Log::error('GHL Custom Values Sync Failed: ' . $response->body());
        return false;
    }

    /**
     * 3. Sync Custom Fields
     */
    private function syncCustomFields(GhlLocation $location): bool
    {

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $location->access_token,
            'Version' => '2021-07-28',
            'Accept' => 'application/json',
        ])->get("https://services.leadconnectorhq.com/locations/{$location->ghl_location_id}/custom-fields");

        if ($response->successful()) {
            $responseData = $response->json();
            $customFields = $responseData['customFields'] ?? $responseData['fields'] ?? (is_array($responseData) && array_is_list($responseData) ? $responseData : []);

            $saved = 0;
            foreach ($customFields as $field) {
                $fieldId = $field['id'] ?? $field['fieldId'] ?? $field['_id'] ?? null;
                if (!$fieldId)
                    continue;

                GhlCustomField::updateOrCreate(
                    [
                        'ghl_location_id' => (string) $location->ghl_location_id,
                        'ghl_field_id' => (string) $fieldId,
                    ],
                    [
                        'name' => $field['name'] ?? null,
                        'field_key' => $field['fieldKey'] ?? null,
                        'data_type' => $field['dataType'] ?? null,
                        'options' => $field['options'] ?? null,
                    ]
                );
                $saved++;
            }
            return $saved > 0;
        }

        Log::error('GHL Custom Fields Sync Failed: ' . $response->body());
        return false;
    }

    /**
     * 4. Sync Tags
     */
    private function syncTags(GhlLocation $location): bool
    {

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $location->access_token,
            'Version' => '2021-07-28',
            'Accept' => 'application/json',
        ])->get("https://services.leadconnectorhq.com/locations/{$location->ghl_location_id}/tags");

        if ($response->successful()) {
            $responseData = $response->json();
            $tags = $responseData['tags'] ?? (is_array($responseData) && array_is_list($responseData) ? $responseData : []);

            $saved = 0;
            foreach ($tags as $tag) {
                $tagId = $tag['id'] ?? $tag['_id'] ?? $tag['tagId'] ?? null;
                if (!$tagId)
                    continue;

                GhlTag::updateOrCreate(
                    [
                        'ghl_location_id' => (string) $location->ghl_location_id,
                        'ghl_tag_id' => (string) $tagId,
                    ],
                    [
                        'name' => $tag['name'] ?? null,
                    ]
                );
                $saved++;
            }
            return $saved > 0;
        }

        Log::error('GHL Tags Sync Failed: ' . $response->body());
        return false;
    }

    /**
     * 5. Sync Pipelines and Stages (Fixed Payload Parsing & Database Execution)
     */
    private function syncPipelines(GhlLocation $location): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $location->access_token,
                'Version' => '2021-07-28',
                'Accept' => 'application/json',
            ])->get("https://services.leadconnectorhq.com/opportunities/pipelines?locationId={$location->ghl_location_id}");

            if (!$response->successful()) {
                Log::error('GHL Pipelines Sync HTTP Error: ' . $response->body());
                return false;
            }

            $responseData = $response->json();

            // Extract Pipelines Array with all potential GHL keys
            $pipelines = [];
            if (isset($responseData['pipelines']) && is_array($responseData['pipelines'])) {
                $pipelines = $responseData['pipelines'];
            } elseif (isset($responseData['pipeline']) && is_array($responseData['pipeline'])) {
                $pipelines = $responseData['pipeline'];
            } elseif (is_array($responseData) && array_is_list($responseData)) {
                $pipelines = $responseData;
            }

            $savedCount = 0;

            foreach ($pipelines as $pipelineData) {
                $pipelineId = $pipelineData['id'] ?? $pipelineData['_id'] ?? $pipelineData['pipelineId'] ?? null;
                if (!$pipelineId)
                    continue;

                $pipeline = GhlPipeline::updateOrCreate(
                    [
                        'ghl_pipeline_id' => (string) $pipelineId,
                    ],
                    [
                        'ghl_location_id' => (string) $location->ghl_location_id,
                        'name' => $pipelineData['name'] ?? 'Unnamed Pipeline',
                    ]
                );

                $savedCount++;

                $stages = $pipelineData['stages'] ?? [];
                foreach ($stages as $index => $stageData) {
                    $stageId = $stageData['id'] ?? $stageData['_id'] ?? $stageData['stageId'] ?? null;
                    if (!$stageId)
                        continue;

                    GhlPipelineStage::updateOrCreate(
                        [
                            'ghl_stage_id' => (string) $stageId,
                        ],
                        [
                            'ghl_location_id' => (string) $location->ghl_location_id,
                            'ghl_pipeline_id' => (string) $pipeline->ghl_pipeline_id,
                            'name' => $stageData['name'] ?? null,
                            'position' => $stageData['position'] ?? $index,
                        ]
                    );
                }
            }

            return $savedCount > 0;

        } catch (\Throwable $e) {
            Log::error('GHL Pipelines Sync Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return false;
        }
    }

    /**
     * Token Expiration Handling & Auto Refresh
     */
    private function getValidAccessToken(GhlLocation $location): ?string
    {
        if ($location->expires_at && $location->expires_at->isPast()) {
            $response = Http::asForm()->post(config('services.marketplace.token_url'), [
                'grant_type' => 'refresh_token',
                'client_id' => config('services.marketplace.client_id'),
                'client_secret' => config('services.marketplace.client_secret'),
                'refresh_token' => $location->refresh_token,
            ]);

            if ($response->successful()) {
                $tokens = $response->json();
                $location->update([
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'] ?? $location->refresh_token,
                    'expires_at' => now()->addSeconds((int) ($tokens['expires_in'] ?? 86400)),
                ]);

                return $location->access_token;
            }

            Log::error('GHL Token Refresh Failed: ' . $response->body());
            return null;
        }

        return $location;
    }

    private function validOAuthState(Request $request): bool
    {
        $state = (string) $request->query('state');
        $sessionState = (string) $request->session()->pull('marketplace_oauth_state');
        $cachedUserId = Cache::pull('marketplace_oauth_state:' . $state);

        return filled($state)
            && hash_equals($sessionState ?: $state, $state)
            && (int) $cachedUserId === (int) $request->user()->id;
    }
}