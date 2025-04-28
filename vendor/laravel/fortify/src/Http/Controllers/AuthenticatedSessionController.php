<?php

namespace Laravel\Fortify\Http\Controllers;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

    // // old and working
    // public function destroy(Request $request): LogoutResponse
    // {
    //     $employeeId = Auth::user()->employeeid;

    //     $this->guard->logout();

    //     if ($request->hasSession()) {
    //         $request->session()->invalidate();
    //         $request->session()->regenerateToken();
    //     }

    //     // Cache::flush(); // Clears all cache (use with caution)

    //     // Clear only user-specific cache keys
    //     Cache::forget('c_authWardCode_' . $employeeId);
    //     Cache::forget('c_locationType_' . $employeeId);
    //     Cache::forget('c_patients_' . $employeeId);
    //     Cache::forget('latest_update_' . $employeeId);

    //     // Optionally clear Inertia session cached data
    //     session()->forget(['cached_inertia_auth', 'cached_inertia_locations']);

    //     return app(LogoutResponse::class);
    // }

    public function destroy(Request $request): LogoutResponse
    {
        if (Auth::check()) {
            $employeeId = Auth::user()->employeeid;

            // Retrieve cached auth ward code
            $authWardCode = Cache::get('c_authWardCode_' . $employeeId);
            // If auth ward code exists, delete the related keys
            if ($authWardCode) {
                Cache::deleteMultiple([
                    'c_authWardCode_' . $employeeId,
                    'c_locationType_' . $employeeId,
                    'c_patients_' . $authWardCode,
                    'c_csr_stocks_' . $authWardCode,
                    'latest_update_' . $authWardCode
                ]);
            }

            // // Clear user-specific cache keys
            // Cache::deleteMultiple([
            //     'c_authWardCode_' . $employeeId,
            //     'c_locationType_' . $employeeId,
            //     'c_patients_' . $employeeId,
            //     'c_csr_stocks_' . $employeeId,
            //     'latest_update_' . $employeeId
            // ]);

            // Clear Inertia session cached data
            session()->forget([
                'cached_inertia_auth',
                'cached_inertia_locations',
                'cached_inertia_fundsource',
            ]);

            // Log out the user
            $this->guard->logout();

            // Invalidate and regenerate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return app(LogoutResponse::class);
    }
}
