<?php

namespace App\Http\Middleware;

use App\Models\FundSource;
use App\Models\Location;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request)
    {
        return parent::version($request);
    }

    protected $middleware = [
        // ... other middlewares
        \Barryvdh\Debugbar\Middleware\InjectDebugbar::class,
    ];

    // // old
    // public function share(Request $request)
    // {
    //     return array_merge(parent::share($request), [
    //         'flash' => [
    //             'message' => fn() => $request->session()->get('message'),
    //             'noItemPrice' => fn() => $request->session()->get('noItemPrice'),
    //         ],
    //         'auth.user.userDetail' => function () use ($request) {
    //             return ($request->user() ? $request->user()->userDetail : null);
    //         },
    //         'auth.user.location' => function () use ($request) {
    //             return ($request->user() ? LoginHistory::with('locationName')->where('employeeid', Auth::user()->employeeid)->orderBy('created_at', 'DESC')->first() : null);
    //         },
    //         'locations' => function () {
    //             return Location::where('wardstat', 'A')->orderBy('wardname', 'ASC')->get();
    //         },

    //         'auth.user.roles' => function () use ($request) {
    //             return ($request->user() ? $request->user()->roles()->pluck('name') : null);
    //         },
    //     ]);
    // }

    // session cached handle inertia
    // public function share(Request $request)
    // {
    //     // Retrieve cached authentication data
    //     $cachedAuthUser = session('cached_inertia_auth');
    //     $cachedLocations = session('cached_inertia_locations');
    //     $cachedFundSource = session('cached_inertia_fundsource');

    //     // If not cached, store it in the session
    //     if (!$cachedAuthUser && $request->user()) {
    //         $cachedAuthUser = [
    //             'userDetail' => $request->user()->userDetail,
    //             'location' => LoginHistory::with('locationName')
    //                 ->where('employeeid', $request->user()->employeeid)
    //                 ->orderBy('created_at', 'DESC')
    //                 ->first(),
    //             'roles' => $request->user()->roles()->pluck('name'),
    //         ];

    //         session(['cached_inertia_auth' => $cachedAuthUser]);
    //         session()->save();
    //     }

    //     // If locations are not cached, fetch and store them
    //     if (!$cachedLocations) {
    //         $cachedLocations = Location::where('wardstat', 'A')
    //             ->orderBy('wardname', 'ASC')
    //             ->get();

    //         session(['cached_inertia_locations' => $cachedLocations]);
    //         session()->save();
    //     }

    //     // If fund source are not cached, fetch and store them
    //     if (!$cachedFundSource) {
    //         $cachedFundSource = FundSource::orderBy('fsName')
    //             ->get(['id', 'fsid', 'fsName', 'cluster_code']);

    //         session(['cached_inertia_fundsource' => $cachedFundSource]);
    //         session()->save();
    //     }

    //     return array_merge(parent::share($request), [
    //         'flash' => [
    //             'message' => fn() => $request->session()->get('message'),
    //             'noItemPrice' => fn() => $request->session()->get('noItemPrice'),
    //         ],
    //         'auth' => [
    //             'user' => fn() => $cachedAuthUser,
    //         ],
    //         'locations' => fn() => $cachedLocations,
    //         'fundSource' => fn() => $cachedFundSource,
    //     ]);
    // }


    public function share(Request $request)
    {
        // Retrieve cached authentication data
        session()->save(); // Ensure session data is persisted before reading

        $cachedAuthUser = session('cached_inertia_auth');
        $cachedLocations = session('cached_inertia_locations');
        $cachedFundSource = session('cached_inertia_fundsource'); // Check correct casing

        // Ensure caching only happens if the session is empty
        if (!$cachedAuthUser && $request->user()) {
            $cachedAuthUser = [
                'userDetail' => $request->user()->userDetail,
                'location' => LoginHistory::with('locationName')
                    ->where('employeeid', $request->user()->employeeid)
                    ->orderBy('created_at', 'DESC')
                    ->first(),
                'roles' => $request->user()->roles()->pluck('name'),
            ];

            session(['cached_inertia_auth' => $cachedAuthUser]);
            session()->save();
        }

        if (!$cachedLocations) {
            $cachedLocations = Location::where('wardstat', 'A')
                ->orderBy('wardname', 'ASC')
                ->get();

            session(['cached_inertia_locations' => $cachedLocations]);
            session()->save();
        }

        if (!$cachedFundSource) {
            $cachedFundSource = FundSource::orderBy('fsName')
                ->get(['id', 'fsid', 'fsName', 'cluster_code']);

            session(['cached_inertia_fundsource' => $cachedFundSource]);
            session()->save();
        }

        return array_merge(parent::share($request), [
            'flash' => [
                'message' => fn() => $request->session()->get('message'),
                'noItemPrice' => fn() => $request->session()->get('noItemPrice'),
            ],
            'auth' => [
                'user' => fn() => $cachedAuthUser,
            ],
            'locations' => fn() => $cachedLocations,
            'fundSource' => fn() => $cachedFundSource,
        ]);
    }
}
