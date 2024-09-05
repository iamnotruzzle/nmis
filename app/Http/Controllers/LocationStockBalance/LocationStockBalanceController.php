<?php

namespace App\Http\Controllers\LocationStockBalance;

use App\Http\Controllers\Controller;
use App\Models\CsrStocks;
use App\Models\LocationStockBalance;
use App\Rules\StockBalanceRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class LocationStockBalanceController extends Controller
{
    public function index(Request $request)
    {
        //   check session
        $hasSession = Sessions::where('id', Session::getId())->exists();

        if ($hasSession) {
            $user = Auth::user();

            $authWardcode = DB::table('csrw_users')
                ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
                ->select('csrw_login_history.wardcode')
                ->where('csrw_login_history.employeeid', $user->employeeid)
                ->orderBy('csrw_login_history.created_at', 'desc')
                ->first();


            Sessions::where('id', Session::getId())->update([
                // 'user_id' => $request->login,
                'location' => $authWardcode->wardcode,
            ]);
        }
        // end check session

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

        // OLD condition. this also add CSR stock balance
        // if ($authWardcode->wardcode == 'CSR') {
        //     $currentStocks = DB::select(
        //         "SELECT clsb_csr.cl2comb as clsb_cl2comb, hc.cl2comb as hc_cl2comb, hc.cl2desc
        //         FROM csrw_csr_stocks as csr
        //         JOIN hclass2 as hc on csr.cl2comb = hc.cl2comb
        //         RIGHT JOIN (
        //             SELECT id, cl2comb, ending_balance, beginning_balance
        //             FROM csrw_location_stock_balance
        //             WHERE location = 'CSR' AND created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
        //         ) AS clsb_csr ON csr.cl2comb = clsb_csr.cl2comb;"
        //     );
        // } else {
        //     $currentStocks =  DB::select(
        //         "SELECT ward.id, clsb_ward.cl2comb as clsb_cl2comb, hc.cl2comb as hc_cl2comb, hc.cl2desc
        //             FROM csrw_wards_stocks as ward
        //             JOIN hclass2 as hc on ward.cl2comb = hc.cl2comb
        //             LEFT JOIN (
        //                 SELECT id, cl2comb, ending_balance, beginning_balance
        //                 FROM csrw_location_stock_balance
        //                 WHERE location = '$authWardcode->wardcode' AND created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
        //             ) AS clsb_ward ON ward.cl2comb = clsb_ward.cl2comb
        //             WHERE [from] = 'CSR' AND location = '$authWardcode->wardcode';"
        //     );
        //     dd($currentStocks);
        // }


        $currentStocks =  DB::select(
            "SELECT ward.id,
                    clsb_ward.cl2comb as clsb_cl2comb,
                    hc.cl2comb as hc_cl2comb,
                    hc.cl2desc
                FROM csrw_wards_stocks as ward
                JOIN hclass2 as hc on ward.cl2comb = hc.cl2comb
                LEFT JOIN (
                    SELECT id, cl2comb, ending_balance, beginning_balance
                    FROM csrw_location_stock_balance
                    WHERE location = '$authWardcode->wardcode'
                        AND created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
                ) AS clsb_ward ON ward.cl2comb = clsb_ward.cl2comb
                WHERE [from] = 'CSR'
                AND quantity > 0
                AND ward.location = '$authWardcode->wardcode'"
        );
        // dd($currentStocks);

        $locationStockBalance = LocationStockBalance::with(['item:cl2comb,cl2desc', 'entry_by', 'updated_by'])
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
            ->whereHas('item', function ($q) use ($searchString) {
                $q->where('cl2desc', 'LIKE', '%' . $searchString . '%');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('Balance/Index', [
            'currentStocks' => $currentStocks,
            'locationStockBalance' => $locationStockBalance,
        ]);

        // // // maintenance page
        // return Inertia::render('UnderMaintenancePage', [
        //     // 'reports' => $reports
        // ]);
    }

    public function store(Request $request)
    {
        // dd($request);

        // TODO add condition to check if the request is to
        // generate ending balance or starting balance

        // $authWardcode = DB::table('csrw_users')
        //     ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
        //     ->select('csrw_login_history.wardcode')
        //     ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
        //     ->orderBy('csrw_login_history.created_at', 'desc')
        //     ->first();

        // $stock = DB::select();

        // return redirect()->back();


        // OLD FUNCTION
        $request->validate(
            [
                'cl2comb' => ['required', new StockBalanceRule($request->cl2comb)],
                'ending_balance' => 'required',
                // 'beginning_balance' => 'required',
            ],
            [
                'cl2comb.required' => 'Item field is required.',
            ]
        );

        LocationStockBalance::create([
            'location' => $request->location,
            'cl2comb' => $request->cl2comb,
            'ending_balance' => $request->ending_balance,
            'beginning_balance' => $request->beginning_balance,
            'entry_by' => $request->entry_by,
        ]);
        return redirect()->back();
    }

    public function update(LocationStockBalance $stockbal, Request $request)
    {
        $request->validate([
            'cl2comb' => 'required',
            'ending_balance' => 'required',
            'beginning_balance' => 'required',
        ]);

        $stockbal->update([
            'location' => $request->location,
            'cl2comb' => $request->cl2comb,
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
