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
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LocationStockBalanceController extends Controller
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

        // check if the latest has a beg bal or ending bal
        $balanceDecChecker = LocationStockBalance::where('location', $authWardcode->wardcode)->OrderBy('created_at', 'DESC')->first();
        // dd($balanceDecChecker);
        $canBeginBalance = null;

        // if true, it can generate beginning balance else it can generate ending balance
        if ($balanceDecChecker == null) {
            $canBeginBalance = true;
        } else if ($balanceDecChecker->beginning_balance == null) {
            $canBeginBalance = true;
        } else {
            $canBeginBalance = false;
        }

        $currentStocks =  DB::select(
            "SELECT ward.id,
                    ward.ris_no,
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

        $locationStockBalance = DB::select(
            "SELECT
                balance.cl2comb,
                item.cl2desc,
                SUM(balance.beginning_balance) AS beginning_balance,
                SUM(balance.ending_balance) AS ending_balance,
                MIN(balance.created_at) AS created_at,
                MIN(balance.beg_bal_created_at) AS beg_bal_created_at,
                MAX(balance.end_bal_created_at) AS end_bal_created_at,
                price.price_per_unit
            FROM
                csrw_location_stock_balance AS balance
            JOIN
                hclass2 AS item ON item.cl2comb = balance.cl2comb
            JOIN
                csrw_item_prices AS price ON price.cl2comb = balance.cl2comb
                AND price.id = balance.price_id  -- Ensure price matching by ID
            WHERE
                balance.created_at >= '$from'
                AND (balance.created_at <= '$to' OR balance.created_at IS NULL)
                AND balance.location = '$authWardcode->wardcode'
            GROUP BY
                balance.cl2comb,
                item.cl2desc,
                price.price_per_unit;"
        );
        // dd($locationStockBalance);

        return Inertia::render('Balance/Index', [
            'currentStocks' => $currentStocks,
            'locationStockBalance' => $locationStockBalance,
            'canBeginBalance' => $canBeginBalance,
        ]);

        // // maintenance page
        // return Inertia::render('UnderMaintenancePage', [
        //     // 'reports' => $reports
        // ]);
    }

    public function store(Request $request)
    {
        $currentStocks = DB::select(
            "SELECT ward.id, ward.location, ward.cl2comb, ward.quantity, ward.ris_no, price.id as price_id
                FROM csrw_wards_stocks as ward
                JOIN csrw_item_prices as price ON price.ris_no = ward.ris_no
                WHERE ward.location = '$request->location'
                -- AND ward.[from] = 'CSR'
                AND ward.quantity > 0"
        );
        // dd($currentStocks);

        // If no balance has been declared before the 12th, create the balance
        $dateTime = Carbon::now();
        if ($request->beg_bal == true) {
            // beginning balance
            foreach ($currentStocks as $stock) {
                LocationStockBalance::create([
                    'location' => $request->location,
                    'cl2comb' => $stock->cl2comb,
                    'beginning_balance' => $stock->quantity,
                    'ris_no' => $stock->ris_no,
                    'price_id' => $stock->price_id,
                    'entry_by' => $request->entry_by,
                    'ward_stock_id' => $stock->id,
                    'beg_bal_created_at' => $dateTime,
                ]);
            }
        } else {
            // ending balance
            foreach ($currentStocks as $stock) {
                LocationStockBalance::create([
                    'location' => $request->location,
                    'cl2comb' => $stock->cl2comb,
                    'ending_balance' => $stock->quantity,
                    'ris_no' => $stock->ris_no,
                    'price_id' => $stock->price_id,
                    'entry_by' => $request->entry_by,
                    'ward_stock_id' => $stock->id,
                    'end_bal_created_at' => $dateTime,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Balance set successfully.');
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
        // $stockbal->delete();

        return redirect()->back();
    }
}
