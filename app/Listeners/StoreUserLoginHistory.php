<?php

namespace App\Listeners;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;

class StoreUserLoginHistory
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $user = User::where('user_name', $this->request->login)->first();
        // dd($user);
        LoginHistory::create([
            // 'employeeid' => $this->request->login,
            'employeeid' => $user->employeeid,
            'wardcode' => $this->request->wardcode
        ]);
        // if $user's designation is csr then create wardcode as csr
        // if $user's designation is ward then create wardcode $this->request->wardcode
        // if $user's designation is super-admin then create wardcode as super-admin /
    }
}
