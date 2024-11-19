<?php

namespace App\Http\Controllers\CsrLocationStockBalance;

use App\Http\Controllers\Controller;
use App\Models\CsrStockBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CsrLocationStockBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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

        return Inertia::render('Balance/Index', [
            'locationStockBalance' => $locationStockBalance,
            // 'canBeginBalance' => $canBeginBalance,
            // 'stockBalDates' => $stockBalDates,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
