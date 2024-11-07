<?php

namespace App\Http\Controllers\LocationStockBalance;

use App\Http\Controllers\Controller;
use App\Models\CsrStocks;
use App\Models\LocationStockBalance;
use App\Models\LocationStockBalanceDateLogs;
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
        $dateRange = $request->date;
        $from = null;
        $to = null;
        $locationStockBalance = null;
        $now = Carbon::now()->format('Y-m-d');

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();
        // dd($authWardcode);

        $stockBalDates = DB::select(
            "SELECT CAST(beg_bal_created_at as DATE) AS beg_bal_date, CAST(end_bal_created_at AS DATE) AS end_bal_date
            FROM csrw_stock_bal_date_logs
            WHERE wardcode = '$authWardcode->wardcode'
            oRDER BY created_at DESC;"
        );
        $default_beg_bal_date = Carbon::parse($stockBalDates[0]->beg_bal_date)->format('Y-m-d');

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

        preg_match('/\[\s*(\d{4}-\d{2}-\d{2})\s*\] - \[\s*(\d{4}-\d{2}-\d{2}|ONGOING)\s*\]/', $dateRange, $matches);
        if ($matches) {
            $from = $matches[1]; // "2024-11-04"
            $to = $matches[2] === 'ONGOING' ? null : $matches[2]; // "2024-11-05" or null if "ONGOING"
        }
        // dd($from);
        // If $to is null, set it to the current date
        $to = $to ?? Carbon::now()->format('Y-m-d');

        if ($from == null) {
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
                balance.location = '$authWardcode->wardcode'
                AND (
                    (CAST(balance.beg_bal_created_at AS DATE) BETWEEN '$default_beg_bal_date' AND '$now')
                    OR balance.beg_bal_created_at IS NULL
                )
                AND (
                    (CAST(balance.end_bal_created_at AS DATE) BETWEEN '$default_beg_bal_date' AND '$now')
                    OR balance.end_bal_created_at IS NULL
                )
            GROUP BY
                balance.cl2comb,
                item.cl2desc,
                price.price_per_unit;"
            );
        } else {
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
                balance.location = '$authWardcode->wardcode'
                AND (
                    (CAST(balance.beg_bal_created_at AS DATE) BETWEEN '$from' AND '$to')
                    OR balance.beg_bal_created_at IS NULL
                )
                AND (
                    (CAST(balance.end_bal_created_at AS DATE) BETWEEN '$from' AND '$to')
                    OR balance.end_bal_created_at IS NULL
                )
            GROUP BY
                balance.cl2comb,
                item.cl2desc,
                price.price_per_unit;"
            );
        }

        return Inertia::render('Balance/Index', [
            'locationStockBalance' => $locationStockBalance,
            'canBeginBalance' => $canBeginBalance,
            'stockBalDates' => $stockBalDates,
        ]);
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

            LocationStockBalanceDateLogs::create([
                'wardcode' => $request->location,
                'beg_bal_created_at' => $dateTime,
            ]);
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

            // Find the last row where wardcode matches and end_bal_created_at is null
            $lastRecord = LocationStockBalanceDateLogs::where('wardcode', $request->location)
                ->whereNull('end_bal_created_at')
                ->latest('id') // or specify another column if 'id' is not the latest indicator
                ->first();

            if ($lastRecord) {
                // Update the end_bal_created_at column
                $lastRecord->update([
                    'end_bal_created_at' => $dateTime, // or specify a custom date if needed
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
