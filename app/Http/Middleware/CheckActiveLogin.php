<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckActiveLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $employeeid = $user->employeeid;

        $isLoggedIn = DB::table('csrw_login_history')
            ->where('employeeid', $employeeid)
            ->exists();

        if (! $isLoggedIn) {
            Auth::logout();

            return redirect()->route('login')->withErrors([
                'message' => 'You have been logged out from another location or session expired.',
            ]);
        }

        return $next($request);
    }
}
