<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckActiveLogin
{
    // public function handle(Request $request, Closure $next)
    // {
    //     $user = Auth::user();

    //     if (!$user) {
    //         return $next($request);
    //     }

    //     $employeeid = $user->employeeid;

    //     $isLoggedIn = DB::table('csrw_login_history')
    //         ->where('employeeid', $employeeid)
    //         ->exists();

    //     if (! $isLoggedIn) {
    //         Auth::logout();

    //         return redirect()->route('login')->withErrors([
    //             'message' => 'You have been logged out from another location or session expired.',
    //         ]);
    //     }

    //     return $next($request);
    // }

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request); // Allow guest
        }

        // Skip Fortify login routes
        if (
            $request->routeIs('login') ||
            $request->is('login') ||
            $request->is('login/*') ||
            session()->has('pending_login_token') // <-- ✅ Allow first request after login
        ) {
            return $next($request);
        }

        $employeeid = $user->employeeid;
        $sessionToken = session('login_token');

        if (!$sessionToken) {
            return $next($request); // Still allow if token not yet stored
        }

        $storedToken = DB::table('csrw_login_history')
            ->where('employeeid', $employeeid)
            ->value('login_token');

        if (!$storedToken || strcasecmp($storedToken, $sessionToken) !== 0) {
            \Log::warning("Token mismatch for employeeid: $employeeid. DB: $storedToken | Session: $sessionToken");

            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'message' => 'You have been logged out because your account was accessed from another location.',
            ]);
        }

        return $next($request);
    }
}
