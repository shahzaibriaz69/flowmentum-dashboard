<?php
namespace App\Http\Controllers;

use App\Models\GhlLocation;
use App\Services\SyncLocationDetailsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationSyncController extends Controller
{
    public function sync(Request $request)
    {
        $user = auth()->user();

        // 1. Fetch location: pehle ghl_location_id match karein, otherwise fallback to user_id
        $location = null;
        $userLocationId = $user->ghl_location_id ?: $user->location_id;
        if (!empty($userLocationId)) {
            $location = GhlLocation::where('ghl_location_id', $userLocationId)->first();
        }
        
        if (!$location) {
            $location = GhlLocation::where('user_id', $user->id)->first();
        }

        // Check missing location or missing OAuth tokens
        if (!$location || empty($location->access_token) || empty($location->refresh_token)) {
            return $this->errorResponse($request, 'OAuth tokens missing or location not connected.', 401);
        }

        // 2. Check if access token is expired (or expires in less than 60 seconds)
        $isExpired = $location->expires_at 
            ? now()->gte($location->expires_at->subSeconds(60))
            : true;

        if ($isExpired) {
            try {
                // Refresh Token Call to GHL API
                $response = Http::asForm()->post('https://services.leadconnectorhq.com/oauth/token', [
                    'client_id'     => config('services.marketplace.client_id'),
                    'client_secret' => config('services.marketplace.client_secret'),
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $location->refresh_token,
                ]);

                if ($response->failed()) {
                    Log::error('GHL Refresh Token Failed', [
                        'status' => $response->status(),
                        'response' => $response->json(),
                    ]);

                    return $this->errorResponse($request, 'Failed to refresh token. Please reconnect location.', 400);
                }

                $data = $response->json();

                // 3. Save NEW Access Token, Refresh Token and Expiration
                if (empty($data['access_token'])) {
                    throw new \RuntimeException('GoHighLevel returned no access token.');
                }

                $location->update([
                    'access_token'  => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? $location->refresh_token,
                    'expires_at'    => now()->addSeconds((int) ($data['expires_in'] ?? 86400)),
                ]);

            } catch (\Exception $e) {
                Log::error('GHL Refresh Token Exception', ['error' => $e->getMessage()]);

                return $this->errorResponse($request, 'Error refreshing authentication token.', 500);
            }
        }

        // // 4. Fetch/Sync latest Location Data from GHL API using active token
        // try {
        //     SyncLocationDetailsService::syncLocationDetails($location);
        // } catch (\Exception $e) {
        //     Log::warning('GHL Details Sync Warning: ' . $e->getMessage());
        // }

         // 4. Fetch/Sync latest Location Data from GHL API using active token
        try {
           $users = SyncLocationDetailsService::syncUsers($location);
           dd($users);
        } catch (\Exception $e) {
            Log::warning('GHL Details Sync Warning: ' . $e->getMessage());
        }

        $result = [
            'status'     => 'success',
            'message'    => 'Location synced successfully!',
            'expires_at' => $location->fresh()->expires_at?->toDateTimeString()
        ];

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return redirect()->route('dashboard')->with('success', $result['message']);
    }

    private function errorResponse(Request $request, string $message, int $status)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], $status);
        }

        return redirect()->route('dashboard')->with('error', $message);
    }
}