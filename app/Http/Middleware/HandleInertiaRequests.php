<?php

namespace App\Http\Middleware;

use App\Models\FundSource;
use App\Models\Item;
use App\Models\Location;
use App\Models\LoginHistory;
use App\Models\RequestStocks;
use App\Models\Supplier;
use App\Models\TypeOfCharge;
use App\Models\UnitOfMeasurement;
use App\Models\UserDetail;
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
        return array_merge(parent::share($request), [
            'flash' => [
                'message' => fn() => $request->session()->get('message'),
                'noItemPrice' => fn() => $request->session()->get('noItemPrice'),
            ],
            // Lazily
            // 'auth.user' => fn () => $request->user()
            //     ? $request->user()->only('id', 'firstName', 'middleName', 'lastName', 'username', 'email', 'image')
            //     : null,
            'auth.user.userDetail' => function () use ($request) {
                return ($request->user() ? $request->user()->userDetail : null);
            },
            'auth.user.location' => function () use ($request) {
                return ($request->user() ? LoginHistory::with('locationName')->where('employeeid', Auth::user()->employeeid)->orderBy('created_at', 'DESC')->first() : null);
                // return LoginHistory::with('locationName')->where('employeeid', Auth::user()->employeeid)->orderBy('created_at', 'DESC')->first();
            },
            // 'items' => function () {
            //     return Item::with('unit:uomcode,uomdesc')->where('cl2stat', 'A')->orderBy('cl2desc', 'ASC')->get();
            // },
            // 'employees' => function () {
            //     return UserDetail::where('empstat', 'A')->orderBy('employeeid', 'ASC')->get(['employeeid', 'empstat']);
            // },
            'locations' => function () {
                return Location::where('wardstat', 'A')->orderBy('wardname', 'ASC')->get();
            },
            // 'suppliers' => function () {
            //     return Supplier::where('suppstat', 'A')->orderBy('suppname', 'ASC')->get(['supplierID', 'suppname', 'suppstat']);
            // },
            'auth.user.permissions' => function () use ($request) {
                return ($request->user() ? $request->user()->getAllPermissions()->pluck('name') : null);
            },
            'auth.user.roles' => function () use ($request) {
                return ($request->user() ? $request->user()->roles()->pluck('name') : null);
            },
            // med supplies
            // 'medSupplies' => function () {
            //     return Item::where('cl2stat', 'A')->orderBy('cl2desc', 'ASC')->get(['cl2comb', 'cl2desc']);
            // },
            //TANKS = drugs and med (oxygen), compressed air, carbon dioxide
            // 'tanksList' => function () {
            //     return DB::select("SELECT cast(hdmhdr.dmdcomb as varchar) + '' + cast(hdmhdr.dmdctr as varchar) as itemcode,
            //                         hdmhdrsub.dmhdrsub, hdmhdrprice.unitcode,
            //                         hgen.gendesc, dmdnost, hdmhdr.dmdnnostp, hstre.stredesc, hform.formdesc, hroute.rtedesc,
            //                         (SELECT TOP 1 dmselprice FROM hdmhdrprice WHERE dmdcomb = hdmhdrsub.dmdcomb ORDER BY dmdprdte DESC) as 'price'
            //                     FROM hdmhdr
            //                     JOIN hdmhdrsub ON hdmhdr.dmdcomb = hdmhdrsub.dmdcomb
            //                     JOIN hdmhdrprice ON hdmhdrsub.dmdcomb = hdmhdrprice.dmdcomb
            //                     JOIN hdruggrp ON hdmhdr.grpcode = hdruggrp.grpcode
            //                     JOIN hgen ON hgen.gencode = hdruggrp.gencode
            //                     JOIN hstre ON hdmhdr.strecode = hstre.strecode
            //                     JOIN hform ON hdmhdr.formcode = hform.formcode
            //                     JOIN hroute ON hdmhdr.rtecode = hroute.rtecode
            //                     WHERE ((hdmhdr.grpcode = '0000000671' )
            //                     OR (hdmhdr.grpcode = '0000000764'
            //                     AND hdmhdrsub.dmhdrsub = 'DRUMD' )
            //                     OR (hdmhdr.dmdcomb = '000000002098'))
            //                     GROUP BY hdmhdr.dmdcomb, hdmhdr.dmdctr, hdmhdrsub.dmhdrsub, hdmhdrprice.unitcode, hdmhdrsub.dmdcomb, hgen.gendesc, hdmhdr.dmdnost, hdmhdr.dmdnnostp, hstre.stredesc, hform.formdesc, hroute.rtedesc;
            //             ");
            // },
            // 'typeOfCharge' => function () {
            //     return TypeOfCharge::where('chrgstat', 'A')
            //         ->where('chrgtable', 'NONDR')
            //         ->get(['chrgcode', 'chrgdesc', 'bentypcod', 'chrgtable']);
            // },
            // 'fundSource' => function () {
            //     return FundSource::get(['id', 'fsid', 'fsName', 'cluster_code']);
            // },
            // 'unitOfMeasurement' => function () {
            //     return UnitOfMeasurement::get(['uomcode', 'uomdesc', 'uomstat']);
            // },



            // 'csrPendingCount' => function () {
            //     return RequestStocks::where('status', 'PENDING')->count();
            // }
        ]);
    }
}
