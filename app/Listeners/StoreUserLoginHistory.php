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
        // dd($this->request);

        $user = User::where('employeeid', $this->request->login)->first();

        LoginHistory::create([
            'employeeid' => $this->request->login,
            'wardcode' => $this->request->wardcode
        ]);

        // if $user's designation is csr then create wardcode as csr
        // if $user's designation is ward then create wardcode $this->request->wardcode
        // if $user's designation is super-admin then create wardcode as super-admin /
    }
}
