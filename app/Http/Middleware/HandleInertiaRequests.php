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


    public function share(Request $request)
    {
        $cachedAuthUser = session('cached_inertia_auth');
        $cachedLocations = session('cached_inertia_locations');
        $cachedFundSource = session('cached_inertia_fundsource');
        // current PENDING and ACKNOWLEDGED RIS
        $pendingAndAckCount = 0;

        // // get auth location
        // dd($cachedAuthUser['location']->wardcode);

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
        }

        // if ($cachedAuthUser['location']->wardcode == 'CSR') {
        //     $pendingAndAckCount = DB::select(
        //         "SELECT count(*) as count FROM csrw_request_stocks WHERE status = 'ACKNOWLEDGED' OR status = 'PENDING';"
        //     );
        // }
        // dd($pendingAndAckCount[0]->count);

        if (!$cachedLocations) {
            $cachedLocations = Location::where('wardstat', 'A')
                ->orderBy('wardname', 'ASC')
                ->get();
        }

        if (!$cachedFundSource) {
            $cachedFundSource = FundSource::orderBy('fsName')
                ->get(['id', 'fsid', 'fsName', 'cluster_code']);
        }

        session([
            'cached_inertia_auth' => $cachedAuthUser,
            'cached_inertia_locations' => $cachedLocations,
            'cached_inertia_fundsource' => $cachedFundSource,
        ]);
        session()->save();

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
            // 'pendingAndAckCount' => fn() => 0,
        ]);
    }
}
