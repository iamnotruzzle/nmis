<?php

namespace App\Http\Controllers\LocationStockBalance;

use App\Http\Controllers\Controller;
use App\Jobs\BegBalWardConsumptionTrackerJobs;
use App\Jobs\EndBalWardConsumptionTrackerJobs;
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
use App\Models\WardStockBalanceSnapshot;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LocationStockBalanceController extends Controller
{
    public function index(Request $request)
    {

        // #region prod
        // $searchString = $request->search;
        // $dateRange = $request->date;
        // $from = null;
        // $to = null;
        // $locationStockBalance = null;

        // $date = Carbon::now();
        // $now = $date->copy()->startOfDay();

        // // get auth wardcode
        // $authWardcode = DB::select(
        //     "SELECT TOP 1
        //         l.wardcode
        //     FROM
        //         user_acc u
        //     INNER JOIN
        //         csrw_login_history l ON u.employeeid = l.employeeid
        //     WHERE
        //         l.employeeid = ?
        //     ORDER BY
        //         l.created_at DESC;
        //     ",
        //     [Auth::user()->employeeid]
        // );
        // // dd($authWardcode);
        // $authCode = $authWardcode[0]->wardcode;

        // $stockBalDates = DB::select(
        //     "SELECT beg_bal_created_at AS beg_bal_date, end_bal_created_at AS end_bal_date
        //     FROM csrw_stock_bal_date_logs
        //     WHERE wardcode = '$authCode'
        //     oRDER BY created_at DESC;"
        // );

        // $default_beg_bal_date = $stockBalDates == null ? null : $stockBalDates[0]->beg_bal_date;

        // // check if the latest has a beg bal or ending bal
        // $balanceDecChecker = LocationStockBalance::where('location', $authCode)->OrderBy('created_at', 'DESC')->first();

        // $canBeginBalance = null;

        // // if true, it can generate beginning balance else it can generate ending balance
        // if ($balanceDecChecker == null) {
        //     $canBeginBalance = true;
        // } else if ($balanceDecChecker->beginning_balance == null) {
        //     $canBeginBalance = true;
        // } else {
        //     $canBeginBalance = false;
        // }

        // preg_match('/\[\s*(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+)\s*\] - \[\s*(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+|ONGOING)\s*\]/', $dateRange, $matches);
        // if ($matches) {
        //     $from = $matches[1]; // "2025-02-24 11:42:52.846"
        //     $to = $matches[2] === 'ONGOING' ? Carbon::now() : $matches[2]; // "2025-02-24 11:43:41.783" or null if "ONGOING"
        // }

        // if ($from == null) {
        //     $locationStockBalance = DB::select(
        //         "SELECT
        //         balance.cl2comb,
        //         item.cl2desc,
        //         SUM(balance.beginning_balance) AS beginning_balance,
        //         SUM(balance.ending_balance) AS ending_balance,
        //         MIN(balance.created_at) AS created_at,
        //         MIN(balance.beg_bal_created_at) AS beg_bal_created_at,
        //         MAX(balance.end_bal_created_at) AS end_bal_created_at,
        //         price.price_per_unit
        //     FROM
        //         csrw_location_stock_balance AS balance
        //     JOIN
        //         hclass2 AS item ON item.cl2comb = balance.cl2comb
        //     JOIN
        //         csrw_item_prices AS price ON price.cl2comb = balance.cl2comb
        //         AND price.id = balance.price_id  -- Ensure price matching by ID
        //     WHERE
        //         balance.location = '$authCode'
        //          AND (
        //             (balance.beg_bal_created_at BETWEEN '$default_beg_bal_date' AND '$now')
        //             OR balance.beg_bal_created_at IS NULL
        //         )
        //         AND (
        //             (balance.end_bal_created_at BETWEEN '$default_beg_bal_date' AND '$now')
        //             OR balance.end_bal_created_at IS NULL
        //         )
        //     GROUP BY
        //         balance.cl2comb,
        //         item.cl2desc,
        //         price.price_per_unit;"
        //     );
        // } else {
        //     $locationStockBalance = DB::select(
        //         "SELECT
        //         balance.cl2comb,
        //         item.cl2desc,
        //         SUM(balance.beginning_balance) AS beginning_balance,
        //         SUM(balance.ending_balance) AS ending_balance,
        //         MIN(balance.created_at) AS created_at,
        //         MIN(balance.beg_bal_created_at) AS beg_bal_created_at,
        //         MAX(balance.end_bal_created_at) AS end_bal_created_at,
        //         price.price_per_unit
        //     FROM
        //         csrw_location_stock_balance AS balance
        //     JOIN
        //         hclass2 AS item ON item.cl2comb = balance.cl2comb
        //     JOIN
        //         csrw_item_prices AS price ON price.cl2comb = balance.cl2comb
        //         AND price.id = balance.price_id  -- Ensure price matching by ID
        //     WHERE
        //         balance.location = '$authCode'
        //         AND (
        //             (balance.beg_bal_created_at BETWEEN '$from' AND '$to')
        //             OR balance.beg_bal_created_at IS NULL
        //         )
        //         AND (
        //             (balance.end_bal_created_at BETWEEN '$from' AND '$to')
        //             OR balance.end_bal_created_at IS NULL
        //         )
        //     GROUP BY
        //         balance.cl2comb,
        //         item.cl2desc,
        //         price.price_per_unit;"
        //     );
        // }
        // // prod
        // return Inertia::render('Balance/Index', [
        //     'locationStockBalance' => $locationStockBalance,
        //     'canBeginBalance' => $canBeginBalance,
        //     'stockBalDates' => $stockBalDates,
        // ]);
        #endregion

        // maintenance page
        return Inertia::render(
            'UnderMaintenancePage',
            []
        );
    }

    public function store(Request $request)
    {
        $date = Carbon::now();
        $begDateTime = $date->copy()->startOfDay(); // Sets time to 00:00:00
        $endDateTime = $date->copy()->endOfDay()->format('Y-m-d H:i:s');   // Sets time to 23:59:59

        $currentStocks = DB::select(
            // "SELECT ward.id, ward.location, ward.cl2comb, ward.quantity, ward.ris_no, price.id as price_id
            //     FROM csrw_wards_stocks as ward
            //     JOIN csrw_item_prices as price ON price.ris_no = ward.ris_no
            //     WHERE ward.location = ?
            //     AND ward.quantity > 0
            //     AND (ward.[from] = 'CSR' OR ward.[from] = 'WARD' OR ward.[from] = 'EXISTING_STOCKS');",
            // [$request->location]

            "SELECT ward_stock.id, ward_stock.stock_id, ward_stock.request_stocks_id, ward_stock.request_stocks_detail_id, ward_stock.stock_id, ward_stock.location, ward_stock.cl2comb,
                    ward_stock.uomcode, ward_stock.chrgcode, ward_stock.quantity, ward_stock.[from], ward_stock.manufactured_date, ward_stock.delivered_date, ward_stock.expiration_date, ward_stock.created_at,
                    ward_stock.ris_no, price.id as price_id
                    FROM csrw_wards_stocks as ward_stock
                    JOIN csrw_item_prices as price ON price.cl2comb = ward_stock.cl2comb AND price.ris_no = ward_stock.ris_no
                    WHERE ward_stock.location = ?",
            [$request->location]
        );
        // dd($currentStocks);

        // $itemCount = DB::select(
        //     "SELECT COUNT(*) as count FROM csrw_wards_stocks WHERE location = ?",
        //     [$request->location]
        // );

        // if ($itemCount[0]->count != 0) {
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
                    'beg_bal_created_at' => $begDateTime,
                ]);


                $id = $stock->id;
                $item_conversion_id = $stock->stock_id;
                $ris_no = $stock->ris_no;
                $cl2comb = $stock->cl2comb;
                $uomcode = $stock->uomcode;
                $quantity = $stock->quantity;
                $location = $stock->location;
                $price_id = $stock->price_id;
                $beg_bal_date = $begDateTime;
                BegBalWardConsumptionTrackerJobs::dispatch(
                    $id,
                    $item_conversion_id,
                    $ris_no,
                    $cl2comb,
                    $uomcode,
                    $location,
                    $price_id,
                    $quantity,
                    $beg_bal_date
                );
            }

            LocationStockBalanceDateLogs::create([
                'wardcode' => $request->location,
                'beg_bal_created_at' => $begDateTime,
            ]);
        } else {
            // dd($currentStocks);

            // $dateTime = Carbon::now();
            $from = null;
            $to = null;

            foreach ($currentStocks as $stock) {
                LocationStockBalance::create([
                    'location' => $request->location,
                    'cl2comb' => $stock->cl2comb,
                    'ending_balance' => $stock->quantity,
                    'ris_no' => $stock->ris_no,
                    'price_id' => $stock->price_id,
                    'entry_by' => $request->entry_by,
                    'ward_stock_id' => $stock->id,
                    'end_bal_created_at' => $endDateTime,
                ]);

                $id = $stock->id;
                $quantity = $stock->quantity;
                $end_bal_date = $endDateTime;
                EndBalWardConsumptionTrackerJobs::dispatch(
                    $id,
                    $quantity,
                    $end_bal_date
                );
                // dd($stock);
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
                ]);
            }


            #region snapshot
            $stockBalDates = DB::select(
                "SELECT beg_bal_created_at AS beg_bal_date, end_bal_created_at AS end_bal_date
                    FROM csrw_stock_bal_date_logs
                    WHERE wardcode = ?
                    oRDER BY created_at DESC;",
                [$request->location]
            );
            // dd($stockBalDates[0]);
            $from = $stockBalDates[0]->beg_bal_date;
            $to = $stockBalDates[0]->end_bal_date;

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

            $ward_report = DB::select(
                "SELECT
                        ward.ris_no,
                        hclass2.cl2comb,
                        hclass2.cl2desc AS cl2desc,
                        huom.uomdesc AS uomdesc,
                        SUM(csrw_location_stock_balance.beginning_balance) AS beginning_balance,
                        SUM(csrw_location_stock_balance.ending_balance) AS ending_balance,
                        csrw_item_prices.price_per_unit AS 'unit_cost',
                        -- Include items from 'CSR' even if charge_quantity is null
                        SUM(CASE WHEN ward.[from] = 'CSR' THEN csrw_location_stock_balance.ending_balance + COALESCE(csrw_patient_charge_logs.charge_quantity, 0) ELSE 0 END) AS 'from_csr',
                        SUM(CASE WHEN ward.[from] = 'WARD' THEN csrw_location_stock_balance.ending_balance + COALESCE(csrw_patient_charge_logs.charge_quantity, 0) ELSE 0 END) AS 'from_ward',
                        SUM(CASE WHEN ward.[from] = 'EXISTING_STOCKS' THEN csrw_location_stock_balance.ending_balance + COALESCE(csrw_patient_charge_logs.charge_quantity, 0) ELSE 0 END) AS 'from_existing',
                        (SELECT SUM(CASE WHEN tscode = 'SURG' THEN quantity ELSE 0 END)
                        FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb AND (cl.created_at BETWEEN '$from' AND '$to')) as 'surgery',
                        (SELECT SUM(CASE WHEN tscode = 'GYNE' THEN quantity ELSE 0 END)
                        FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb AND (cl.created_at BETWEEN '$from' AND '$to')) as 'obgyne',
                        (SELECT SUM(CASE WHEN tscode = 'ORTHO' THEN quantity ELSE 0 END)
                        FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb AND (cl.created_at BETWEEN '$from' AND '$to')) as 'ortho',
                        (SELECT SUM(CASE WHEN tscode = 'PEDIA' THEN quantity ELSE 0 END)
                        FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb AND (cl.created_at BETWEEN '$from' AND '$to')) as 'pedia',
                        (SELECT SUM(CASE WHEN tscode = 'OPHTH' THEN quantity ELSE 0 END)
                        FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb AND (cl.created_at BETWEEN '$from' AND '$to')) as 'optha',
                        (SELECT SUM(CASE WHEN tscode = 'ENT' THEN quantity ELSE 0 END)
                        FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb AND (cl.created_at BETWEEN '$from' AND '$to')) as 'ent',
                        csrw_patient_charge_logs.charge_quantity as total_consumption,
                        SUM(csrw_ward_transfer_stock.transferred_qty) as transferred_qty
                    FROM
                        csrw_wards_stocks AS ward
                    JOIN hclass2 ON ward.cl2comb = hclass2.cl2comb
                    JOIN csrw_item_prices ON csrw_item_prices.ris_no = ward.ris_no
                    LEFT JOIN huom ON ward.uomcode = huom.uomcode
                    LEFT JOIN (
                        SELECT ward_stocks_id, SUM(quantity) AS charge_quantity
                        FROM csrw_patient_charge_logs
                        WHERE pcchrgdte BETWEEN '$from' AND '$to'
                        GROUP BY ward_stocks_id
                    ) csrw_patient_charge_logs ON ward.id = csrw_patient_charge_logs.ward_stocks_id
                    LEFT JOIN csrw_location_stock_balance ON csrw_location_stock_balance.ward_stock_id = ward.id
                    LEFT JOIN (
                        SELECT ward_stock_id, SUM(quantity) AS transferred_qty
                        FROM csrw_ward_transfer_stock
                        WHERE status = 'RECEIVED'
                        GROUP BY ward_stock_id
                    ) csrw_ward_transfer_stock ON ward.id = csrw_ward_transfer_stock.ward_stock_id
                    WHERE
                        ward.location LIKE '$authCode'
                        AND ward.is_consumable IS NULL
                        AND (
                            (csrw_location_stock_balance.beg_bal_created_at BETWEEN '$from' AND '$to')
                            OR csrw_location_stock_balance.beg_bal_created_at IS NULL
                        )
                        AND (
                            (csrw_location_stock_balance.end_bal_created_at BETWEEN '$from' AND '$to')
                            OR csrw_location_stock_balance.end_bal_created_at IS NULL
                        )
                        AND ward.is_consumable IS NULL
                        AND (
                            ward.[from] = 'CSR' OR  ward.[from] = 'WARD' OR  ward.[from] = 'EXISTING_STOCKS'
                        )
                    GROUP BY
                        hclass2.cl2comb,
                        hclass2.cl2desc,
                        huom.uomdesc,
                        csrw_item_prices.price_per_unit,
                        ward.ris_no,
                        csrw_patient_charge_logs.charge_quantity
                    ORDER BY
                        hclass2.cl2desc ASC;"
            );

            $result = $this->processReport($ward_report);

            foreach ($result as $row) {
                $logs[] = WardStockBalanceSnapshot::create([
                    'cl2comb' => $row->cl2comb,
                    'item_description' => $row->item_description,
                    'unit' => $row->unit,
                    'unit_cost' => $row->unit_cost,
                    'beginning_balance' => $row->beginning_balance,
                    'from_csr' => $row->from_csr == null ? 0 : $row->from_csr,
                    'from_ward' => $row->from_ward == null ? 0 : $row->from_ward,
                    'total_beg_bal' => $row->total_beg_bal == null ? 0 : $row->total_beg_bal,
                    'surgery' => $row->surgery == null ? 0 : $row->surgery,
                    'obgyne' => $row->obgyne == null ? 0 : $row->obgyne,
                    'ortho' => $row->ortho == null ? 0 : $row->ortho,
                    'pedia' => $row->pedia == null ? 0 : $row->pedia,
                    'optha' => $row->optha == null ? 0 : $row->optha,
                    'ent' => $row->ent == null ? 0 : $row->ent,
                    'total_consumption' => $row->total_consumption == null ? 0 : $row->total_consumption,
                    'total_cons_estimated_cost' => $row->total_cons_estimated_cost == null ? 0 : $row->total_cons_estimated_cost,
                    'transferred_qty' => $row->transferred_qty == null ? 0 : $row->transferred_qty,
                    'ending_balance' => $row->ending_balance == null ? 0 : $row->ending_balance,
                    'wardcode' => $row->wardcode,
                ]);
            }
            #endregion snapshot
        }
        // }

        return redirect()->back()->with('success', 'Balance set successfully.');
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
