<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckLocation
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check location from all possible sources
        $locationId = session('active_location_id') 
            ?? $user->ghl_location_id 
            ?? $user->location_id 
            ?? null;

        // If location is missing in user, try fetching first available location from DB fallback
        if (!$locationId) {
            $locationId = \App\Models\GhlLocation::first()?->ghl_location_id;
            if ($locationId) {
                session(['active_location_id' => $locationId]);
            }
        }

        if (!$locationId) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Location missing or unauthorized access.');
        }

        return $next($request);
    }
}