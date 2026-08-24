<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckLocation
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Agar user login nahi hai to login page par bhejen
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 2. Check karein ke user ke paas location_id majood hai ya nahi
        if (empty($user->location_id)) {
            // Un-authorized user ko logout karke login page par redirect kar dein
            Auth::logout();
            return redirect()->route('login')->with('error', 'Location missing or unauthorized access.');
        }

        return $next($request);
    }
}