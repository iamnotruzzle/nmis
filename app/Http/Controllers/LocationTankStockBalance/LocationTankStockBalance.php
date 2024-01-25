<?php

namespace App\Http\Controllers\LocationTankStockBalance;

use App\Http\Controllers\Controller;
use App\Models\LocationStockBalance;
use App\Models\LocationTankStockBalance as ModelsLocationTankStockBalance;
use App\Rules\TankStockBalanceRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LocationTankStockBalance extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;

        $currentStocks = null;

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();


        // $currentStocks =  DB::select(
        //     "SELECT clsb_ward.cl2comb as clsb_cl2comb, hc.cl2comb as hc_cl2comb, hc.cl2desc
        //             FROM csrw_wards_stocks_med_supp as ward
        //             JOIN hclass2 as hc on ward.cl2comb = hc.cl2comb
        //             left JOIN (
        //                 SELECT id, cl2comb, ending_balance, beginning_balance
        //                 FROM csrw_location_stock_balance
        //                 WHERE location = '$authWardcode->wardcode' AND created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
        //             ) AS clsb_ward ON ward.cl2comb = clsb_ward.cl2comb
        //             WHERE [from] =  'CSR' AND location = '$authWardcode->wardcode'
        //             GROUP BY hc.cl2comb, clsb_ward.cl2comb, hc.cl2desc;"
        // );


        $locationStockBalance = LocationStockBalance::with(['item:cl2comb,cl2desc', 'entry_by', 'updated_by'])
            ->where('location', $authWardcode->wardcode)
            ->when(
                $request->from,
                function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }
            )
            ->when(
                $request->to,
                function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }
            )
            ->whereHas('item', function ($q) use ($searchString) {
                $q->where('cl2desc', 'LIKE', '%' . $searchString . '%');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('Balance/Index', [
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

        LocationStockBalance::create([
            'location' => $request->location,
            'itemcode' => $request->itemcode,
            'ending_balance' => $request->ending_balance,
            'beginning_balance' => $request->beginning_balance,
            'entry_by' => $request->entry_by,
        ]);

        return redirect()->back();
    }

    public function update(LocationStockBalance $stockbal, Request $request)
    {
        $request->validate([
            'itemcode' => 'required',
            'ending_balance' => 'required',
            'beginning_balance' => 'required',
        ]);

        $stockbal->update([
            'location' => $request->location,
            'itemcode' => $request->itemcode,
            'ending_balance' => $request->ending_balance,
            'beginning_balance' => $request->beginning_balance,
            'updated_by' => $request->entry_by,
        ]);

        // dd($lsb);

        return redirect()->back();
    }

    public function destroy(LocationStockBalance $stockbal)
    {
        $stockbal->delete();

        return redirect()->back();
    }
}
