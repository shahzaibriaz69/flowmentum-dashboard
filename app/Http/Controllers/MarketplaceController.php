<?php

namespace App\Http\Controllers;

use App\Models\GhlLocation;
use App\Models\GhlUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

            return redirect()->route('dashboard')->with('error', 'GoHighLevel did not return an authorization code. Check the configured redirect URL.');
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

        // --- STEP 1: SYNC GHL USERS ---
        if (!$this->syncUsers($location)) {
            return redirect()->route('dashboard')->with(
                'error',
                'Location connected, but GHL users permission is missing. Please reconnect after enabling users.readonly in the GHL Marketplace app.'
            );
        }

        return redirect()->route('dashboard')->with('success', 'GoHighLevel location connected and users synced successfully.');
    }

    /**
     * Sync Location Button Action (Route: location.sync)
     */
   public function syncLocation(Request $request): RedirectResponse
{
    $user = $request->user();


    $location = GhlLocation::where('ghl_location_id', $user->location_id)
        ->orWhere('user_id', $user->id)
        ->first();

    if (!$location) {
        dd('DEBUG ERROR 1: No Location record found in ghl_locations table for User ID: ' . $user->id);
    }

    $token = $this->getValidAccessToken($location);
    if (!$token) {
        dd('DEBUG ERROR 2: Access Token is empty or Refresh Token failed for Location ID: ' . $location->ghl_location_id);
    }

  
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'Version'       => '2021-07-28',
        'Accept'        => 'application/json',
    ])->get("https://services.leadconnectorhq.com/users/?locationId={$location->ghl_location_id}");

    if (!$response->successful()) {
        dd('DEBUG ERROR 3: GHL API Call Failed!', [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);
    }


    $users = $response->json()['users'] ?? [];
    dd('DEBUG SUCCESS: GHL API working!', [
        'fetched_users_count' => count($users),
        'users_data'          => $users,
    ]);
}

    /**
     * GHL Users Fetch and Database Insertion
     */
    private function syncUsers(GhlLocation $location): bool
    {
        $token = $this->getValidAccessToken($location);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Version' => '2021-07-28',
            'Accept' => 'application/json',
        ])->get("https://services.leadconnectorhq.com/users/?locationId={$location->ghl_location_id}");

        if ($response->successful()) {
            $users = $response->json()['users'] ?? [];

            foreach ($users as $userData) {
                GhlUser::updateOrCreate(
                    ['ghl_user_id' => $userData['id']],
                    [
                        'user_id' => $location->user_id,
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
            }

            return true;
        }

        Log::error('GHL Users Sync Failed: ' . $response->body());
        return false;
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

        return $location->access_token;
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