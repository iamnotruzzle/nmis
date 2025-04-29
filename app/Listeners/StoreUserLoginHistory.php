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

        LoginHistory::where('employeeid', $user->employeeid)
            ->delete();

        LoginHistory::create([
            'employeeid' => $user->employeeid,
            'wardcode' => $this->request->wardcode
        ]);
    }
}
