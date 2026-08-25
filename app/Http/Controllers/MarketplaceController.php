<?php

namespace App\Http\Controllers;

use App\Models\GhlLocation;
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
            'redirect_uri'  => config('services.marketplace.callback_url'),
            'client_id'     => config('services.marketplace.client_id'),
            'state'         => session('marketplace_oauth_state'),
            'scope'         => 'contacts.readonly contacts.write locations.readonly locations/customValues.readonly locations/customValues.write opportunities.readonly opportunities.write calendars.readonly calendars.write calendars/events.readonly calendars/events.write calendars/groups.readonly calendars/groups.write calendars/resources.readonly calendars/resources.write locations/tags.readonly locations/tags.write',
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

        if (! $request->filled('code')) {
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

        return redirect()->route('dashboard')->with('success', 'GoHighLevel location connected successfully.');
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
