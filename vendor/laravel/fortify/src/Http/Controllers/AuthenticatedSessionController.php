<?php

namespace Laravel\Fortify\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LoginViewResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    /**
     * Show the login view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Fortify\Contracts\LoginViewResponse
     */
    public function create(Request $request): LoginViewResponse
    {
        return app(LoginViewResponse::class);
    }

    /**
     * Attempt to authenticate a new session.
     *
     * @param  \Laravel\Fortify\Http\Requests\LoginRequest  $request
     * @return mixed
     */
    public function store(LoginRequest $request)
    {
        return $this->loginPipeline($request)->then(function ($request) {
            return app(LoginResponse::class);
        });
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param  \Laravel\Fortify\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Pipeline\Pipeline
     */
    protected function loginPipeline(LoginRequest $request)
    {
        if (Fortify::$authenticateThroughCallback) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                call_user_func(Fortify::$authenticateThroughCallback, $request)
            ));
        }

        if (is_array(config('fortify.pipelines.login'))) {
            return (new Pipeline(app()))->send($request)->through(array_filter(
                config('fortify.pipelines.login')
            ));
        }

        return (new Pipeline(app()))->send($request)->through(array_filter([
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
            Features::enabled(Features::twoFactorAuthentication()) ? RedirectIfTwoFactorAuthenticatable::class : null,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]));
    }

    // OLD
    // public function destroy(Request $request): LogoutResponse
    // {
    //     // if (Auth::check()) {
    //     $employeeId = Auth::user()->employeeid;

    //     $authWardcode = DB::select(
    //         "SELECT TOP 1
    //             l.wardcode
    //             FROM
    //                 user_acc u
    //             INNER JOIN
    //                 csrw_login_history l ON u.employeeid = l.employeeid
    //             WHERE
    //                 l.employeeid = ?
    //             ORDER BY
    //                 l.created_at DESC;
    //             ",
    //         [$employeeId]
    //     );
    //     $authCode = $authWardcode[0]->wardcode;

    //     // Retrieve cached auth ward code
    //     // $authWardCode_employeeId = Cache::get($authCode . $employeeId);
    //     // $authWardCode_employeeId = Cache::get($authCode);
    //     // If auth ward code exists, delete the related keys
    //     if ($authCode) {
    //         Cache::deleteMultiple([
    //             // 'c_authWardCode_' . $employeeId,
    //             // 'c_locationType_' . $employeeId,
    //             'c_patients_' . $authCode,
    //             'c_csr_stocks_' . $authCode,
    //             'latest_update_' . $authCode
    //         ]);
    //     }

    //     LoginHistory::where('employeeid', $employeeId)->delete();

    //     // Clear Inertia session cached data
    //     session()->forget([
    //         'cached_inertia_auth',
    //         // 'cached_inertia_locations',
    //         'cached_inertia_fundsource',
    //     ]);

    //     // Log out the user
    //     $this->guard->logout();

    //     // Invalidate and regenerate session
    //     $request->session()->invalidate();
    //     $request->session()->regenerateToken();
    //     // }

    //     return app(LogoutResponse::class);
    // }

    //NEW
    public function destroy(Request $request): LogoutResponse
    {
        // Check if user is authenticated before accessing properties
        if (Auth::check() && Auth::user()) {
            $employeeId = Auth::user()->employeeid;

            $authWardcode = DB::select(
                "SELECT TOP 1
                l.wardcode
                FROM
                    user_acc u
                INNER JOIN
                    csrw_login_history l ON u.employeeid = l.employeeid
                WHERE
                    l.employeeid = ?
                ORDER BY
                    l.created_at DESC;
                ",
                [$employeeId]
            );

            if (!empty($authWardcode)) {
                $authCode = $authWardcode[0]->wardcode;

                // Clear cache only if we have a valid auth code
                if ($authCode) {
                    Cache::deleteMultiple([
                        'c_patients_' . $authCode,
                        'c_csr_stocks_' . $authCode,
                        'latest_update_' . $authCode,
                        'transaction_data_ward_' . $authCode
                    ]);
                }
            }

            // Clean up login history
            LoginHistory::where('employeeid', $employeeId)->delete();
        }

        // Clear Inertia session cached data
        session()->forget([
            'cached_inertia_auth',
            'cached_inertia_fundsource',
        ]);

        // Log out the user (this is safe even if user is null)
        $this->guard->logout();

        // Invalidate and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return app(LogoutResponse::class);
    }
}
