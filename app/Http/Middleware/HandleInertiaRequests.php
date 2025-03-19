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
        // Retrieve cached authentication data
        // session()->save(); // Ensure session data is persisted before reading
        //a

        $cachedAuthUser = session('cached_inertia_auth');
        $cachedPackages = session('cached_inertia_packages');
        $cachedLocations = session('cached_inertia_locations');
        $cachedFundSource = session('cached_inertia_fundsource'); // Check correct casing
        $cachedSuppliers = session('cached_inertia_suppliers');

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

        if (!$cachedPackages) {
            $cachedPackages = DB::select(
                "SELECT package.id, package.description, pack_dets.cl2comb, item.cl2desc, pack_dets.quantity, package.status
                    FROM csrw_packages AS package
                    JOIN csrw_package_details as pack_dets ON pack_dets.package_id = package.id
                    JOIN hclass2 as item ON item.cl2comb = pack_dets.cl2comb
                    WHERE package.status = 'A'
                    ORDER BY item.cl2desc ASC;"
            );

            session(['cached_inertia_packages' => $cachedPackages]);
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

        if (!$cachedSuppliers) {
            $cachedSuppliers = PimsSupplier::where('status', 'A')->orderBy('suppname', 'ASC')->get();

            session(['cached_inertia_suppliers' => $cachedSuppliers]);
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
            'packages' => fn() => $cachedPackages,
            'suppliers' => fn() => $cachedSuppliers,
        ]);
    }
}
