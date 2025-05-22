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
use App\Models\WardConsumptionTracker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LocationStockBalanceController extends Controller
{
    public function index(Request $request)
    {

        #region prod
        $searchString = $request->search;
        $dateRange = $request->date;
        $from = null;
        $to = null;
        $locationStockBalance = null;

        $date = Carbon::now();
        $now = $date->copy()->startOfDay();

        #region auth ward code and ward location type
        $authWardcode = DB::select(
            "SELECT TOP 1
                l.wardcode
                FROM
                    user_acc u
                INNER JOIN
                    csrw_login_history l ON u.employeeid = l.employeeid
                WHERE
                    l.employeeid = ?
                ORDER BY
                    l.created_at DESC;
                ",
            [Auth::user()->employeeid]
        );
        $authCode = $authWardcode[0]->wardcode;

        // Get the latest balance date logs for the ward
        $stockBalDates = DB::select(
            "SELECT beg_bal_created_at AS beg_bal_date, end_bal_created_at AS end_bal_date
                FROM csrw_stock_bal_date_logs
                WHERE wardcode = ?
                ORDER BY created_at DESC
        ",
            [$authCode]
        );

        // Default values
        $default_beg_bal_date = $stockBalDates[0]->beg_bal_date ?? null;
        $default_end_bal_date = $stockBalDates[0]->end_bal_date ?? null;

        $latestDateLog = LocationStockBalanceDateLogs::where('wardcode', $authCode)
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

        $locationStockBalance = DB::select(
            "SELECT
                    tracker.cl2comb,
                    item.cl2desc,
                    price.price_per_unit,
                    SUM(tracker.beg_bal_qty) AS beginning_balance,
                    SUM(tracker.end_bal_qty) AS ending_balance,
                    MIN(tracker.beg_bal_date) AS beg_bal_created_at,
                    MAX(tracker.end_bal_date) AS end_bal_created_at
                FROM csrw_ward_consumption_tracker AS tracker
                JOIN hclass2 AS item ON item.cl2comb = tracker.cl2comb
                JOIN csrw_item_prices AS price ON price.id = tracker.price_id
                WHERE tracker.location = ?
                    AND (
                        (tracker.beg_bal_date BETWEEN ? AND ?)
                        OR tracker.beg_bal_date IS NULL
                    )
                    AND (
                        (tracker.end_bal_date BETWEEN ? AND ?)
                        OR tracker.end_bal_date IS NULL
                    )
                GROUP BY
                    tracker.cl2comb,
                    item.cl2desc,
                    price.price_per_unit
            ",
            [
                $authCode,
                $from,
                $to,
                $from,
                $to
            ]
        );

        // // prod
        return Inertia::render('Balance/Index', [
            'locationStockBalance' => $locationStockBalance,
            'canBeginBalance' => $canBeginBalance,
            'stockBalDates' => $stockBalDates,
        ]);
        #endregion

        /////////////////////////////
        // // maintenance page
        // return Inertia::render(
        //     'UnderMaintenancePage',
        //     []
        // );
    }

    public function store(Request $request)
    {
        $entry_by = Auth::user()->employeeid;
        $date = Carbon::now();
        // $begDateTime = $date->copy()->startOfDay(); // Sets time to 00:00:00
        // $endDateTime = $date->copy()->endOfDay()->format('Y-m-d H:i:s');   // Sets time to 23:59:59
        $begDateTime = $date->copy(); // Sets time to 00:00:00
        $endDateTime = $date->copy();   // Sets time to 23:59:59

        // old
        // $currentStocks = DB::select(
        //     "SELECT ward_stock.id, ward_stock.stock_id, ward_stock.request_stocks_id, ward_stock.request_stocks_detail_id, ward_stock.stock_id, ward_stock.location, ward_stock.cl2comb,
        //             ward_stock.uomcode, ward_stock.chrgcode, ward_stock.quantity, ward_stock.[from], ward_stock.manufactured_date, ward_stock.delivered_date, ward_stock.expiration_date, ward_stock.created_at,
        //             ward_stock.ris_no, price.id as price_id
        //             FROM csrw_wards_stocks as ward_stock
        //             JOIN csrw_item_prices as price ON price.cl2comb = ward_stock.cl2comb AND price.ris_no = ward_stock.ris_no
        //             WHERE ward_stock.location = ?
        //             AND ward_stock.quantity > 0",
        //     [$request->location]
        // );

        // new ONLY read stocks that have request stock status as received or NULL(meaning its added using existing stock function)
        $currentStocks = DB::select(
            "SELECT ward_stock.id, ward_stock.stock_id, ward_stock.request_stocks_id, ward_stock.request_stocks_detail_id, ward_stock.stock_id, ward_stock.location, ward_stock.cl2comb,
                ward_stock.uomcode, ward_stock.chrgcode, ward_stock.quantity, ward_stock.[from], ward_stock.manufactured_date, ward_stock.delivered_date, ward_stock.expiration_date, ward_stock.created_at,
                ward_stock.ris_no, price.id as price_id
                FROM csrw_wards_stocks as ward_stock
            JOIN csrw_item_prices as price ON price.cl2comb = ward_stock.cl2comb AND price.ris_no = ward_stock.ris_no
            LEFT JOIN csrw_request_stocks rs ON rs.id = ward_stock.request_stocks_id
            WHERE ward_stock.location = ?
            AND ward_stock.quantity > 0
            AND (rs.id IS NULL OR rs.status = 'RECEIVED');",
            [$request->location] // Duplicate the parameter
        );
        // dd($currentStocks);

        // if ($itemCount[0]->count != 0) {
        if ($request->beg_bal == true) {
            // beginning balance
            foreach ($currentStocks as $stock) {
                // dd($stock);
                $id = $stock->id;
                $item_conversion_id = $stock->stock_id;
                $ris_no = $stock->ris_no;
                $cl2comb = $stock->cl2comb;
                $uomcode = $stock->uomcode;
                $quantity = $stock->quantity;
                $initial_qty = $stock->quantity;
                $location = $stock->location;
                $price_id = $stock->price_id;
                $from = $stock->from;
                $beg_bal_date = $begDateTime;

                $this->beginningBalanceForTrackerLog(
                    $id,
                    $item_conversion_id,
                    $ris_no,
                    $cl2comb,
                    $uomcode,
                    $location,
                    $price_id,
                    $quantity,
                    $initial_qty,
                    $from,
                    $beg_bal_date
                );
            }

            //
            LocationStockBalanceDateLogs::create([
                'wardcode' => $request->location,
                'beg_bal_created_at' => $begDateTime,
                'beg_bal_declared_by' => $entry_by,
            ]);
        } else {
            foreach ($currentStocks as $stock) {
                $id = $stock->id;
                $quantity = $stock->quantity;
                $price_id = $stock->price_id;
                $cl2comb = $stock->cl2comb;
                $price_id = $stock->price_id;
                $end_bal_date = $endDateTime;

                $this->endingBalanceForTrackerLog(
                    $id,
                    $cl2comb,
                    $quantity,
                    $price_id,
                    $end_bal_date
                );
            }

            // Find the last row where wardcode matches and end_bal_created_at is null
            $lastRecord = LocationStockBalanceDateLogs::where('wardcode', $request->location)
                ->whereNull('end_bal_created_at')
                ->latest('id') // or specify another column if 'id' is not the latest indicator
                ->first();

            if ($lastRecord) {
                // Update the end_bal_created_at column
                $lastRecord->update([
                    'end_bal_created_at' => $endDateTime, // or specify a custom date if needed
                    'end_bal_declared_by' => $entry_by,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Balance set successfully.');
    }

    public function beginningBalanceForTrackerLog($id, $item_conversion_id, $ris_no, $cl2comb, $uomcode, $location, $price_id, $quantity, $initial_qty, $from, $beg_bal_date)
    {
        $date = Carbon::now();

        // Step 1: Get the latest balance period for this ward
        $currentPeriod = DB::table('csrw_stock_bal_date_logs')
            ->where('wardcode', $location)
            ->latest('beg_bal_created_at')
            ->first();

        // Step 2: Get latest ACTIVE tracker row for this stock
        $tracker = WardConsumptionTracker::where('ward_stock_id', $id)
            ->where('cl2comb', $cl2comb)
            ->where('price_id', $price_id)
            ->where('location', $location)
            ->where('status', NULL)
            ->latest('created_at')
            ->first();

        if ($tracker) {
            // 🔁 Update existing active tracker with beginning balance info
            $tracker->update([
                'beg_bal_date' => $beg_bal_date,
                'beg_bal_qty' => $quantity,
            ]);
        } else {
            // 🆕 No active tracker — create a new one
            WardConsumptionTracker::create([
                'ward_stock_id' => $id,
                'item_conversion_id' => $item_conversion_id,
                'ris_no' => $ris_no,
                'cl2comb' => $cl2comb,
                'uomcode' => $uomcode,
                'beg_bal_date' => $beg_bal_date,
                'beg_bal_qty' => $quantity,
                'initial_qty' => $quantity,
                'item_from' => $from,
                'location' => $location,
                'price_id' => $price_id,
                'status' => NULL,
            ]);
        }
    }

    public function endingBalanceForTrackerLog($id, $cl2comb, $quantity, $price_id, $end_bal_date)
    {

        $tracker = WardConsumptionTracker::where('ward_stock_id', $id)
            ->where('cl2comb', $cl2comb)
            ->where('price_id', $price_id)
            ->whereNull('end_bal_date')    // Only open ones
            ->latest('created_at')
            ->first();

        if ($tracker) {
            // Set ending balance regardless of whether beginning balance exists
            $tracker->update([
                'end_bal_qty'  => $quantity,
                'end_bal_date' => $end_bal_date,
                'status' => 'closed',
            ]);
        } else {
            // Optional: log or handle if there's no tracker to update
            \Log::warning("No open tracker found for ending balance", [
                'ward_stock_id' => $id,
                'cl2comb'       => $cl2comb,
                'price_id'      => $price_id,
            ]);
        }
    }

    public function processReport($ward_report)
    {

        //region get auth ward code
        $authWardcode = DB::select(
            "SELECT TOP 1
                l.wardcode
                FROM
                    user_acc u
                INNER JOIN
                    csrw_login_history l ON u.employeeid = l.employeeid
                WHERE
                    l.employeeid = ?
                ORDER BY
                    l.created_at DESC;
                ",
            [Auth::user()->employeeid]
        );
        // dd($authWardcode);
        $authCode = $authWardcode[0]->wardcode;
        //endregion

        $combinedReports = [];
        $loopCount  = 0;

        foreach ($ward_report as $e) {
            $loopCount++;
            // Create a unique key based on cl2comb and unit_cost
            $key = $e->cl2comb . '-' . $e->unit_cost;

            // If this key already exists, combine the values
            if (isset($combinedReports[$key])) {
                $combinedReports[$key]->beginning_balance += $e->beginning_balance;
                $combinedReports[$key]->from_csr += $e->from_csr;
                $combinedReports[$key]->from_ward += $e->from_ward;
                $combinedReports[$key]->total_beg_bal += $e->from_csr + $e->from_ward + $e->from_existing;
                // $combinedReports[$key]->surgery += $e->surgery;
                // $combinedReports[$key]->obgyne += $e->obgyne;
                // $combinedReports[$key]->ortho += $e->ortho;
                // $combinedReports[$key]->pedia += $e->pedia;
                // $combinedReports[$key]->optha += $e->optha;
                // $combinedReports[$key]->ent += $e->ent;
                $combinedReports[$key]->total_consumption += $e->total_consumption;
                $combinedReports[$key]->total_cons_estimated_cost += $e->total_consumption * $e->unit_cost;
                $combinedReports[$key]->transferred_qty += $e->transferred_qty;
                $combinedReports[$key]->ending_balance += $e->ending_balance;
            } else {
                // If key doesn't exist, create a new object
                $combinedReports[$key] = (object) [
                    'cl2comb' => $e->cl2comb,
                    'item_description' => $e->cl2desc,
                    'unit' => $e->uomdesc,
                    'unit_cost' => $e->unit_cost,
                    'beginning_balance' => $e->beginning_balance,
                    'from_csr' => $e->from_csr,
                    'from_ward' => $e->from_ward,
                    'total_beg_bal' => $e->from_csr + $e->from_ward + $e->from_existing,
                    'surgery' => $e->surgery,
                    'obgyne' => $e->obgyne,
                    'ortho' => $e->ortho,
                    'pedia' => $e->pedia,
                    'optha' => $e->optha,
                    'ent' => $e->ent,
                    'total_consumption' => $e->total_consumption,
                    'total_cons_estimated_cost' => $e->total_consumption * $e->unit_cost,
                    'transferred_qty' => $e->transferred_qty,
                    'ending_balance' => $e->ending_balance,
                    'actual_inventory' => 0,
                    'wardcode' => $authCode
                ];
            }
        }
        // Convert the combined associative array into a regular array of objects
        $reports = array_values($combinedReports);

        return $reports;
    }

    public function update()
    {
        return redirect()->back();
    }

    public function destroy()
    {
        return redirect()->back();
    }
}
