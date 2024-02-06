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
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'flash' => [
                'message' => fn () => $request->session()->get('message')
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
            'items' => function () {
                return Item::with('unit:uomcode,uomdesc')->where('cl2stat', 'A')->orderBy('cl2desc', 'ASC')->get();
            },
            'employees' => function () {
                return UserDetail::where('empstat', 'A')->orderBy('employeeid', 'ASC')->get();
            },
            'locations' => function () {
                return Location::where('wardstat', 'A')->orderBy('wardname', 'ASC')->get();
            },
            'suppliers' => function () {
                return Supplier::where('suppstat', 'A')->orderBy('suppname', 'ASC')->get(['suppcode', 'suppname', 'suppstat']);
            },
            'auth.user.permissions' => function () use ($request) {
                return ($request->user() ? $request->user()->getAllPermissions()->pluck('name') : null);
            },
            'auth.user.roles' => function () use ($request) {
                return ($request->user() ? $request->user()->roles()->pluck('name') : null);
            },
            // med supplies
            'medSupplies' => function () {
                return Item::where('cl2stat', 'A')->orderBy('cl2desc', 'ASC')->get(['cl2comb', 'cl2desc']);
            },
            // TANKS = drugs and med (oxygen), compressed air, carbon dioxide
            'tanksList' => function () {
                return DB::select("SELECT cast(hdmhdr.dmdcomb as varchar) + '' + cast(hdmhdr.dmdctr as varchar) as itemcode,
                                    hdmhdrsub.dmhdrsub,
                                    (SELECT TOP 1 unitcode FROM hdmhdrprice WHERE dmdcomb = hdmhdrsub.dmdcomb ORDER BY dmdprdte DESC) as 'unitcode',
                                    cast(hgen.gendesc as varchar) + ' ' + cast(dmdnost as varchar) + ' ' + cast(hdmhdr.dmdnnostp as varchar) + ' ' + cast(hstre.stredesc as varchar) + ' ' + cast(hform.formdesc as varchar) + ' ' + cast(hroute.rtedesc as varchar) AS itemDesc,
                                    (SELECT TOP 1 dmselprice FROM hdmhdrprice WHERE dmdcomb = hdmhdrsub.dmdcomb ORDER BY dmdprdte DESC) as 'price'
                                FROM hdmhdr
                                JOIN hdmhdrsub ON hdmhdr.dmdcomb = hdmhdrsub.dmdcomb
                                JOIN hdmhdrprice ON hdmhdrsub.dmdcomb = hdmhdrprice.dmdcomb
                                JOIN hdruggrp ON hdmhdr.grpcode = hdruggrp.grpcode
                                JOIN hgen ON hgen.gencode = hdruggrp.gencode
                                JOIN hstre ON hdmhdr.strecode = hstre.strecode
                                JOIN hform ON hdmhdr.formcode = hform.formcode
                                JOIN hroute ON hdmhdr.rtecode = hroute.rtecode
                                WHERE ((hdmhdr.grpcode = '0000000671' )
                                OR (hdmhdr.grpcode = '0000000764'
                                AND hdmhdrsub.dmhdrsub = 'DRUMD' )
                                OR (hdmhdr.dmdcomb = '000000002098'))
                                AND hdmhdrsub.stockbal != 0
                                GROUP BY hdmhdr.dmdcomb, hdmhdr.dmdctr, hdmhdrsub.dmhdrsub, hgen.gendesc, dmdnost, hdmhdr.dmdnnostp, hstre.stredesc, hform.formdesc, hroute.rtedesc, hdmhdrsub.dmdcomb;
                        ");
            },
            'typeOfCharge' => function () {
                return TypeOfCharge::where('chrgstat', 'A')
                    ->where('chrgtable', 'NONDR')
                    ->get(['chrgcode', 'chrgdesc', 'bentypcod', 'chrgtable']);
            },
            'fundSource' => function () {
                return FundSource::get(['id', 'fsid', 'fsName', 'cluster_code']);
            },
            'unitOfMeasurement' => function () {
                return UnitOfMeasurement::get(['uomcode', 'uomdesc', 'uomstat']);
            },
            // 'csrPendingCount' => function () {
            //     return RequestStocks::where('status', 'PENDING')->count();
            // }
        ]);
    }
}
