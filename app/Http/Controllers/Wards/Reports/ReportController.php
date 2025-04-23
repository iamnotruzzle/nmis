<?php

namespace App\Http\Controllers\Wards\Reports;

use App\Http\Controllers\Controller;
use App\Models\LocationStockBalanceDateLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Sessions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class ReportController extends Controller
{

    public function index(Request $request)
    {
        #region prod
        $dateRange = $request->date;
        $from = null;
        $to = null;
        $locationStockBalance = null;

        $date = Carbon::now();
        $now = $date->copy()->startOfDay();

        // get auth wardcode
        // Define cache keys for ward code and location type based on the authenticated user's employee ID
        $cache_authWardCode = 'c_authWardCode_' . Auth::user()->employeeid;
        // Attempt to retrieve cached ward code and location type
        $authWardCode_cached = Cache::get($cache_authWardCode);

        // Get the latest balance date logs for the ward
        $stockBalDates = DB::select(
            "SELECT beg_bal_created_at AS beg_bal_date, end_bal_created_at AS end_bal_date
                FROM csrw_stock_bal_date_logs
                WHERE wardcode = ?
                ORDER BY created_at DESC
        ",
            [$authWardCode_cached]
        );

        // Default values
        $default_beg_bal_date = $stockBalDates[0]->beg_bal_date ?? null;
        $default_end_bal_date = $stockBalDates[0]->end_bal_date ?? null;

        $latestDateLog = LocationStockBalanceDateLogs::where('wardcode', $authWardCode_cached)
            ->latest('created_at')->first();
        // dd($latestDateLog);
        if ($latestDateLog == null) {
            $canBeginBalance = true;
        } else if ($latestDateLog != null && $latestDateLog->end_bal_created_at != null) {
            $canBeginBalance = true;
        } else {
            $canBeginBalance = false;
        }

        // Date range parsing
        $from = $default_beg_bal_date;
        $to = $default_end_bal_date ?? Carbon::now(); // default to ongoing if end date is null

        if (!empty($dateRange)) {
            preg_match('/\[\s*(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(?:\.\d+)?)\s*\] - \[\s*(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(?:\.\d+)?|ONGOING)\s*\]/', $dateRange, $matches);
            if ($matches) {
                $from = $matches[1] ?? $default_beg_bal_date;
                $to = $matches[2] === 'ONGOING' ? Carbon::now() : ($matches[2] ?? $default_end_bal_date);
            }
        }

        // $result = DB::select(
        //     "SELECT
        //             tracker.cl2comb,
        //             item.cl2desc as item_description,
        //             unit.uomdesc as unit,
        //             price.price_per_unit as unit_cost,
        //             SUM(tracker.beg_bal_qty) as beginning_balance,
        //             SUM(CASE WHEN tracker.item_from IN ('CSR', 'EXISTING_STOCKS') THEN tracker.initial_qty ELSE 0 END) as from_csr,
        //             SUM(CASE WHEN tracker.item_from = 'WARD' THEN tracker.initial_qty ELSE 0 END) as from_ward,
        //             SUM(tracker.beg_bal_qty) as total_beg_balance,
        //             SUM(tracker.surgery) as surgery,
        //             SUM(tracker.obgyne) as obgyne,
        //             SUM(tracker.ortho) as ortho,
        //             SUM(tracker.pedia) as pedia,
        //             SUM(tracker.ent) as ent,
        //             SUM(tracker.non_specific_charge) as total_consumption,
        //             SUM(price.price_per_unit * (tracker.non_specific_charge + tracker.surgery + tracker.obgyne + tracker.ortho + tracker.pedia + tracker.ent)) as total_estimated_cost,
        //             SUM(tracker.transfer_qty) as total_transfers,
        //             SUM(tracker.end_bal_qty) as ending_balance
        //         FROM
        //             csrw_ward_consumption_tracker as tracker
        //         JOIN
        //             hclass2 as item on item.cl2comb = tracker.cl2comb
        //         JOIN
        //             huom as unit ON unit.uomcode = tracker.uomcode
        //         JOIN
        //             csrw_item_prices as price ON price.id = tracker.price_id
        //         WHERE
        //             tracker.location = ?
        //             AND (
        //                 (tracker.beg_bal_date IS NOT NULL AND tracker.beg_bal_date BETWEEN ? AND ?)
        //                 OR (tracker.beg_bal_date IS NULL AND tracker.end_bal_date IS NOT NULL AND tracker.end_bal_date BETWEEN ? AND ?)
        //             )
        //         GROUP BY
        //             tracker.cl2comb,
        //             item.cl2desc,
        //             unit.uomdesc,
        //             price.price_per_unit
        //         ORDER BY
        //             item.cl2desc,
        //             price.price_per_unit;",
        //     [$authWardCode_cached, $from, $to, $from, $to]
        // );

        $result = DB::select(
            "SELECT
                tracker.cl2comb,
                item.cl2desc as item_description,
                unit.uomdesc as unit,
                price.price_per_unit as unit_cost,
                SUM(tracker.beg_bal_qty) as beginning_balance,
                SUM(CASE
                    WHEN tracker.item_from IN ('CSR', 'EXISTING_STOCKS')
                    AND tracker.created_at BETWEEN ? AND ?
                    THEN tracker.initial_qty
                    ELSE 0
                END) as from_csr,
                SUM(CASE
                    WHEN tracker.item_from = 'WARD'
                    AND tracker.created_at BETWEEN ? AND ?
                    THEN tracker.initial_qty
                    ELSE 0
                END) as from_ward,
                SUM(tracker.beg_bal_qty) as total_beg_balance,
                SUM(tracker.surgery) as surgery,
                SUM(tracker.obgyne) as obgyne,
                SUM(tracker.ortho) as ortho,
                SUM(tracker.pedia) as pedia,
                SUM(tracker.ent) as ent,
                SUM(tracker.non_specific_charge) as total_consumption,
                SUM(price.price_per_unit * (tracker.non_specific_charge + tracker.surgery + tracker.obgyne + tracker.ortho + tracker.pedia + tracker.ent)) as total_estimated_cost,
                SUM(tracker.transfer_qty) as total_transfers,
                SUM(tracker.end_bal_qty) as ending_balance
            FROM
                csrw_ward_consumption_tracker as tracker
            JOIN
                hclass2 as item on item.cl2comb = tracker.cl2comb
            JOIN
                huom as unit ON unit.uomcode = tracker.uomcode
            JOIN
                csrw_item_prices as price ON price.id = tracker.price_id
            WHERE
                tracker.location = ?
                AND (
                    (tracker.beg_bal_date IS NOT NULL AND tracker.beg_bal_date BETWEEN ? AND ?)
                    OR (tracker.beg_bal_date IS NULL AND tracker.end_bal_date IS NOT NULL AND tracker.end_bal_date BETWEEN ? AND ?)
                )
            GROUP BY
                tracker.cl2comb,
                item.cl2desc,
                unit.uomdesc,
                price.price_per_unit
            ORDER BY
                item.cl2desc,
                price.price_per_unit;",
            [$from, $to, $from, $to, $authWardCode_cached, $from, $to, $from, $to]
        );
        $reports = array_values($result);
        // dd($reports);

        // // prod
        return Inertia::render('Wards/Reports/Consumption/Index', [
            'reports' => $reports,
            'locationStockBalance' => $locationStockBalance,
            'stockBalDates' => $stockBalDates,
        ]);
        // #endregion prod

        // // maintenance page
        // return Inertia::render('UnderMaintenancePage', [
        //     'UnderMaintenancePage',
        //     []
        // ]);
    }
}
