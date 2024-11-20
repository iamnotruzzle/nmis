<?php

namespace App\Http\Controllers\CsrLocationStockBalance;

use App\Http\Controllers\Controller;
use App\Models\CsrStockBalance;
use App\Models\CsrStockbalDateLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CsrLocationStockBalanceController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;
        $dateRange = $request->date;
        $from = null;
        $to = null;
        $locationStockBalance = null;
        $now = Carbon::now()->format('Y-m-d');

        $stockBalDates = DB::select(
            "SELECT CAST(beg_bal_created_at as DATE) AS beg_bal_date, CAST(end_bal_created_at AS DATE) AS end_bal_date
            FROM csrw_csr_stock_bal_date_logs
            oRDER BY created_at DESC;"
        );
        // dd($stockBalDates);
        $default_beg_bal_date = $stockBalDates == [] ? Carbon::now()->format('Y-m-d') : Carbon::parse($stockBalDates[0]->beg_bal_date)->format('Y-m-d');

        // check if the latest has a beg bal or ending bal
        $balanceDecChecker = CsrStockBalance::OrderBy('created_at', 'DESC')->first();
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
                    csrw_csr_stock_balance AS balance
                JOIN
                    hclass2 AS item ON item.cl2comb = balance.cl2comb
                JOIN
                    csrw_item_prices AS price ON price.cl2comb = balance.cl2comb
                    AND price.id = balance.price_id  -- Ensure price matching by ID
                WHERE
                    (
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
                    csrw_csr_stock_balance AS balance
                JOIN
                    hclass2 AS item ON item.cl2comb = balance.cl2comb
                JOIN
                    csrw_item_prices AS price ON price.cl2comb = balance.cl2comb
                    AND price.id = balance.price_id  -- Ensure price matching by ID
                WHERE
                    (
                        (CAST(balance.beg_bal_created_at AS DATE) BETWEEN '$from' AND '$now')
                        OR balance.beg_bal_created_at IS NULL
                    )
                    AND (
                        (CAST(balance.end_bal_created_at AS DATE) BETWEEN '$from' AND '$now')
                        OR balance.end_bal_created_at IS NULL
                    )
                GROUP BY
                    balance.cl2comb,
                    item.cl2desc,
                    price.price_per_unit;"
            );
        }

        return Inertia::render('CsrStockBal/Index', [
            'locationStockBalance' => $locationStockBalance,
            'canBeginBalance' => $canBeginBalance,
            'stockBalDates' => $stockBalDates,
        ]);
    }

    public function store(Request $request)
    {
        // orig query
        // $currentStocks = DB::select(
        //     "SELECT stock.id, stock.cl2comb_after as cl2comb, stock.quantity_after as quantity, stock.ris_no, price.id as price_id
        //         FROM csrw_csr_item_conversion as stock
        //         JOIN csrw_item_prices as price ON price.ris_no = stock.ris_no
        //         WHERE stock.quantity_after > 0;"
        // );
        $currentStocks = DB::select(
            "SELECT stock.id, stock.cl2comb_after as cl2comb, stock.quantity_after as quantity, stock.ris_no, price.id as price_id
                FROM csrw_csr_item_conversion as stock
                JOIN csrw_item_prices as price ON price.ris_no = stock.ris_no;"
        );
        // dd($currentStocks);

        // If no balance has been declared before the 12th, create the balance
        $dateTime = Carbon::now();
        if ($request->beg_bal == true) {
            // beginning balance
            foreach ($currentStocks as $stock) {
                CsrStockBalance::create([
                    'cl2comb' => $stock->cl2comb,
                    'beginning_balance' => $stock->quantity,
                    'ris_no' => $stock->ris_no,
                    'price_id' => $stock->price_id,
                    'entry_by' => $request->entry_by,
                    'converted_id' => $stock->id,
                    'beg_bal_created_at' => $dateTime,
                ]);
            }

            CsrStockbalDateLogs::create([
                'beg_bal_created_at' => $dateTime,
            ]);
        } else {
            // ending balance
            foreach ($currentStocks as $stock) {
                CsrStockBalance::create([
                    'cl2comb' => $stock->cl2comb,
                    'ending_balance' => $stock->quantity,
                    'ris_no' => $stock->ris_no,
                    'price_id' => $stock->price_id,
                    'entry_by' => $request->entry_by,
                    'converted_id' => $stock->id,
                    'end_bal_created_at' => $dateTime,
                ]);
            }

            // Find the last row where wardcode matches and end_bal_created_at is null
            $lastRecord = CsrStockbalDateLogs::whereNull('end_bal_created_at')
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

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
