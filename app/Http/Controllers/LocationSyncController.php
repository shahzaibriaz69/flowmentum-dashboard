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

        $userLocationIds = $user->locationId;
        if (!empty($userLocationId)) {
            $locations = GhlLocation::whereIn('ghl_location_id', $userLocationIds)->get();
        }
        foreach ($locations as $location) {
            if (!$location || empty($location->access_token) || empty($location->refresh_token)) {
                return $this->errorResponse($request, 'OAuth tokens missing or location not connected.', 401);
            }

            // 2. Check if access token is expired (or expires in less than 60 seconds)
            $isExpired = $location->expires_at
                ? now()->gte($location->expires_at->subSeconds(60))
                : true;

            if ($isExpired) {
                try {

                    $response = Http::asForm()->post('https://services.leadconnectorhq.com/oauth/token', [
                        'client_id' => config('services.marketplace.client_id'),
                        'client_secret' => config('services.marketplace.client_secret'),
                        'grant_type' => 'refresh_token',
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

                    if (empty($data['access_token'])) {
                        throw new \RuntimeException('GoHighLevel returned no access token.');
                    }

                    $location->update([
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'] ?? $location->refresh_token,
                        'expires_at' => now()->addSeconds((int) ($data['expires_in'] ?? 86400)),
                    ]);

                } catch (\Exception $e) {
                    Log::error('GHL Refresh Token Exception', ['error' => $e->getMessage()]);

                    return $this->errorResponse($request, 'Error refreshing authentication token.', 500);
                }
            }

            // 4. Fetch and persist the latest GHL users, contacts, opportunities, and appointments using the active token
            try {
                $syncedUsersCount = SyncLocationDetailsService::syncUsers($location);
                $syncedContactsCount = SyncLocationDetailsService::syncContacts($location);
                $syncedOpportunitiesCount = SyncLocationDetailsService::syncOpportunities($location);
                $syncedAppointmentsCount = SyncLocationDetailsService::syncAppointments($location);
            } catch (\Exception $e) {
                Log::error('GHL Users Sync Failed', ['error' => $e->getMessage()]);

                if (str_contains($e->getMessage(), 'not authorized for this scope')) {
                    return $this->errorResponse(
                        $request,
                        'GHL users permission missing. Please reconnect the location to grant users.readonly access.',
                        403
                    );
                }

                return $this->errorResponse($request, 'Unable to sync GHL users, contacts, opportunities, or appointments.', 502);
            }

            $result = [
                'status' => 'success',
                'message' => "Location synced successfully! {$syncedUsersCount} users, {$syncedContactsCount} contacts, {$syncedOpportunitiesCount} opportunities, and {$syncedAppointmentsCount} appointments saved.",
                'synced_users_count' => $syncedUsersCount,
                'synced_contacts_count' => $syncedContactsCount,
                'synced_opportunities_count' => $syncedOpportunitiesCount,
                'synced_appointments_count' => $syncedAppointmentsCount,
                'expires_at' => $location->fresh()->expires_at?->toDateTimeString()
            ];

            if ($request->expectsJson()) {
                return response()->json($result);
            }
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