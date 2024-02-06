<?php

namespace App\Http\Controllers\LocationTankStockBalance;

use App\Http\Controllers\Controller;
use App\Models\LocationTankStockBalance;
use App\Rules\TankStockBalanceRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LocationTankStockBalanceController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        $currentStocks = null;

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        // dd($authWardcode);

        $currentStocks =  DB::select(
            "SELECT clsb_ward.itemcode as clsb_itemcode, hc.itemcode as hc_itemcode, hc.itemDesc
            FROM csrw_wards_stocks_tanks_supp as ward
            JOIN (
                SELECT cast(hdmhdr.dmdcomb as varchar) + '' + cast(hdmhdr.dmdctr as varchar) as itemcode,
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
                GROUP BY hdmhdr.dmdcomb, hdmhdr.dmdctr, hdmhdrsub.dmhdrsub, hgen.gendesc, dmdnost, hdmhdr.dmdnnostp, hstre.stredesc, hform.formdesc, hroute.rtedesc, hdmhdrsub.dmdcomb
            ) AS hc on ward.itemcode = hc.itemcode
            left JOIN (
                SELECT id, itemcode, ending_balance, beginning_balance
                FROM csrw_location_tank_stock_balance
                WHERE location = '$authWardcode->wardcode' AND created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
            ) AS clsb_ward ON ward.itemcode = clsb_ward.itemcode
            WHERE [from] =  'CSR' AND location = '$authWardcode->wardcode'
            GROUP BY hc.itemcode, clsb_ward.itemcode, hc.itemDesc;"
        );
        // dd($currentStocks);


        $locationStockBalance = LocationTankStockBalance::with(['entry_by', 'updated_by'])
            ->where('location', $authWardcode->wardcode)
            ->when(
                $request->from,
                function ($query, $value) use ($from) {
                    $query->whereDate('created_at', '>=', $from);
                }
            )
            ->when(
                $request->to,
                function ($query, $value) use ($to) {
                    $query->whereDate('created_at', '<=', $to);
                }
            )
            // ->whereHas('item', function ($q) use ($searchString) {
            //     $q->where('cl2desc', 'LIKE', '%' . $searchString . '%');
            // })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('TankBalance/Index', [
            'currentStocks' => $currentStocks,
            'locationStockBalance' => $locationStockBalance,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'itemcode' => ['required', new TankStockBalanceRule($request->itemcode)],
                'ending_balance' => 'required',
                'beginning_balance' => 'required',
            ],
            [
                'itemcode.required' => 'Item field is required.',
            ]
        );

        LocationTankStockBalance::create([
            'location' => $request->location,
            'itemcode' => $request->itemcode,
            'ending_balance' => $request->ending_balance,
            'beginning_balance' => $request->beginning_balance,
            'entry_by' => $request->entry_by,
        ]);

        return redirect()->back();
    }

    public function update(LocationTankStockBalance $tankstockbal, Request $request)
    {
        $request->validate([
            'itemcode' => 'required',
            'ending_balance' => 'required',
            'beginning_balance' => 'required',
        ]);

        $tankstockbal->update([
            'location' => $request->location,
            'itemcode' => $request->itemcode,
            'ending_balance' => $request->ending_balance,
            'beginning_balance' => $request->beginning_balance,
            'updated_by' => $request->entry_by,
        ]);

        // dd($lsb);

        return redirect()->back();
    }

    public function destroy(LocationTankStockBalance $tankstockbal)
    {
        $tankstockbal->delete();

        return redirect()->back();
    }
}
