<?php

namespace App\Http\Responses;

use App\Models\LoginHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Session;

class LoginResponse implements LoginResponseContract
{

    // public function toResponse($request)
    // {
    //     $home = (Auth::user()->designation == 'admin') ? 'admindashboard' : ((Auth::user()->designation == 'csr') ? 'csrdashboard' : 'warddashboard');
    //     return redirect()->to($home);
    // }

    public function toResponse($request)
    {
        $user = Auth::user();

        if (session()->has('pending_login_token')) {
            // ✅ Normalize to lowercase to avoid mismatch
            $loginToken = strtolower(session('pending_login_token'));
            $wardcode = session('pending_wardcode');

            // ✅ Save lowercase token to session
            session(['login_token' => $loginToken]);
            session()->forget(['pending_login_token', 'pending_wardcode']);

            // ✅ Save lowercase token to DB
            \Log::info("Saving login history", ['employeeid' => $user->employeeid, 'token' => $loginToken]);

            // DB::table('csrw_login_history')->updateOrInsert(
            //     ['employeeid' => $user->employeeid],
            //     ['login_token' => $loginToken, 'wardcode' => $wardcode]
            // );
            LoginHistory::updateOrCreate(
                ['employeeid' => $user->employeeid],
                ['login_token' => $loginToken, 'wardcode' => $wardcode]
            );

            session()->save(); // Ensure it's saved before redirect
        }

        $home = match ($user->designation) {
            'admin' => 'admindashboard',
            'csr' => 'csrdashboard',
            default => 'warddashboard',
        };

        return redirect()->to($home);
    }
}
