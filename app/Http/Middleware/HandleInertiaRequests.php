<?php

namespace App\Http\Middleware;

use App\Models\FundSource;
use App\Models\Location;
use App\Models\LoginHistory;
use App\Models\PimsSupplier;
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
    //     $cachedAuthUser = session('cached_inertia_auth');
    //     $cachedFundSource = session('cached_inertia_fundsource');
    //     // current PENDING and ACKNOWLEDGED RIS
    //     $pendingAndAckCount = 0;

    //     // Ensure caching only happens if the session is empty
    //     if (!$cachedAuthUser && $request->user()) {
    //         $cachedAuthUser = [
    //             'userDetail' => $request->user()->userDetail,
    //             'location' => LoginHistory::with('locationName')
    //                 ->where('employeeid', $request->user()->employeeid)
    //                 ->orderBy('created_at', 'DESC')
    //                 ->first(),
    //             'roles' => $request->user()->roles()->pluck('name'),
    //         ];
    //     }

    //     $pendingAndAckCount = DB::select(
    //         "SELECT count(*) as count FROM csrw_request_stocks WHERE status = 'ACKNOWLEDGED' OR status = 'PENDING';"
    //     );

    //     if (!$cachedFundSource) {
    //         $cachedFundSource = FundSource::orderBy('fsName')
    //             ->get(['id', 'fsid', 'fsName', 'cluster_code']);
    //     }

    //     session([
    //         'cached_inertia_auth' => $cachedAuthUser,
    //         'cached_inertia_fundsource' => $cachedFundSource,
    //     ]);
    //     session()->save();


    //     return array_merge(parent::share($request), [
    //         'flash' => [
    //             'message' => fn() => $request->session()->get('message'),
    //             'noItemPrice' => fn() => $request->session()->get('noItemPrice'),
    //         ],
    //         'auth' => [
    //             'user' => fn() => $cachedAuthUser,
    //         ],
    //         'fundSource' => fn() => $cachedFundSource,
    //         'pendingAndAckCount' => fn() => $pendingAndAckCount[0]->count,
    //     ]);
    // }

    // // new
    public function share(Request $request)
    {
        $cachedAuthUser = session('cached_inertia_auth');
        $cachedFundSource = session('cached_inertia_fundsource');

        // Only query and cache if not already cached
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
        }

        if (!$cachedFundSource) {
            $cachedFundSource = FundSource::orderBy('fsName')
                ->get(['id', 'fsid', 'fsName', 'cluster_code']);

            session(['cached_inertia_fundsource' => $cachedFundSource]);
        }

        // This query runs every time (you might want to cache this too)
        $pendingAndAckCount = 0; // default value
        if ($cachedAuthUser && isset($cachedAuthUser['location'])) {
            $locationName = $cachedAuthUser['location']->locationName ?? null;

            if ($locationName && strtoupper($locationName->wardcode) === 'CSR') {
                $result = DB::select(
                    "SELECT count(*) as count FROM csrw_request_stocks WHERE status = 'ACKNOWLEDGED' OR status = 'PENDING';"
                );
                $pendingAndAckCount = $result[0]->count ?? 0;
            }
        }

        return array_merge(parent::share($request), [
            'flash' => [
                'message' => fn() => $request->session()->get('message'),
                'noItemPrice' => fn() => $request->session()->get('noItemPrice'),
            ],
            'auth' => [
                'user' => fn() => $cachedAuthUser,
            ],
            'fundSource' => fn() => $cachedFundSource,
            // 'pendingAndAckCount' => fn() => $pendingAndAckCount[0]->count,
            'pendingAndAckCount' => fn() => $pendingAndAckCount,
        ]);
    }
}
