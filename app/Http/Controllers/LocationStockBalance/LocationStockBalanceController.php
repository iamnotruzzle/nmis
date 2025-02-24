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
use App\Models\WardStockBalanceSnapshot;
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

        // dd($dateRange);

        // get auth wardcode
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

        $stockBalDates = DB::select(
            "SELECT CAST(beg_bal_created_at as DATE) AS beg_bal_date, CAST(end_bal_created_at AS DATE) AS end_bal_date
            FROM csrw_stock_bal_date_logs
            WHERE wardcode = '$authCode'
            oRDER BY created_at DESC;"
        );
        // dd($stockBalDates);
        $default_beg_bal_date = $stockBalDates == [] ? Carbon::now()->format('Y-m-d') : Carbon::parse($stockBalDates[0]->beg_bal_date)->format('Y-m-d');

        // check if the latest has a beg bal or ending bal
        $balanceDecChecker = LocationStockBalance::where('location', $authCode)->OrderBy('created_at', 'DESC')->first();
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
                balance.location = '$authCode'
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
                balance.location = '$authCode'
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

        // // maintenance page
        // return Inertia::render('UnderMaintenancePage', []);
    }

    public function store(Request $request)
    {
        $currentStocks = DB::select(
            "SELECT ward.id, ward.location, ward.cl2comb, ward.quantity, ward.ris_no, price.id as price_id
                FROM csrw_wards_stocks as ward
                JOIN csrw_item_prices as price ON price.ris_no = ward.ris_no
                WHERE ward.location = '$request->location'
                AND ward.[from] = 'CSR' OR ward.[from] = 'WARD'"
        );

        $itemCount = DB::select(
            "SELECT COUNT(*) as count FROM csrw_wards_stocks WHERE location = ?",
            [$request->location]
        );

        if ($itemCount[0]->count != 0) {
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
                // dd('ending');

                $dateTime = Carbon::now();

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

                $stockBalDates = DB::select(
                    "SELECT CAST(beg_bal_created_at as DATE) AS beg_bal_date, CAST(end_bal_created_at AS DATE) AS end_bal_date
                        FROM csrw_stock_bal_date_logs
                        WHERE wardcode = ?
                        ORDER BY created_at DESC;",
                    [$request->location]
                );
                $default_beg_bal_date = $stockBalDates == [] ? Carbon::now()->format('Y-m-d') : Carbon::parse($stockBalDates[0]->beg_bal_date)->format('Y-m-d');
                // dd($default_beg_bal_date);

                //get ward report (LATEST report)
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
                        (SELECT SUM(CASE WHEN tscode = 'SURG' THEN quantity ELSE 0 END)
                        FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'surgery',
                        (SELECT SUM(CASE WHEN tscode = 'GYNE' THEN quantity ELSE 0 END)
                        FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'obgyne',
                        (SELECT SUM(CASE WHEN tscode = 'ORTHO' THEN quantity ELSE 0 END)
                        FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ortho',
                        (SELECT SUM(CASE WHEN tscode = 'PEDIA' THEN quantity ELSE 0 END)
                        FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'pedia',
                        (SELECT SUM(CASE WHEN tscode = 'OPHTH' THEN quantity ELSE 0 END)
                        FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'optha',
                        (SELECT SUM(CASE WHEN tscode = 'ENT' THEN quantity ELSE 0 END)
                        FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ent',
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
                        WHERE CAST(pcchrgdte AS DATE) BETWEEN '$default_beg_bal_date' AND '$dateTime'
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
                        ward.location LIKE ?
                        AND ward.is_consumable IS NULL
                        AND (
                            (CAST(csrw_location_stock_balance.beg_bal_created_at AS DATE) BETWEEN '$default_beg_bal_date' AND '$dateTime')
                            OR csrw_location_stock_balance.beg_bal_created_at IS NULL
                        )
                        AND (
                            (CAST(csrw_location_stock_balance.end_bal_created_at AS DATE) BETWEEN '$default_beg_bal_date' AND '$dateTime')
                            OR csrw_location_stock_balance.end_bal_created_at IS NULL
                        AND ward.is_consumable IS NULL)
                        AND (
                            ward.[from] = 'CSR' OR  ward.[from] = 'WARD'
                        )
                    GROUP BY
                        hclass2.cl2comb,
                        hclass2.cl2desc,
                        huom.uomdesc,
                        csrw_item_prices.price_per_unit,
                        ward.ris_no,
                        csrw_patient_charge_logs.charge_quantity
                    ORDER BY
                        hclass2.cl2desc ASC;",
                    [$request->location]
                );

                $result = $this->processReport($ward_report);

                foreach ($result as $row) {
                    $logs[] = WardStockBalanceSnapshot::create([
                        'cl2comb' => $row->cl2comb,
                        'item_description' => $row->item_description,
                        'unit' => $row->unit,
                        'unit_cost' => $row->unit_cost,
                        'beginning_balance' => $row->beginning_balance,
                        'from_csr' => $row->from_csr,
                        'from_ward' => $row->from_ward,
                        'total_beg_bal' => $row->total_beg_bal,
                        'surgery' => $row->surgery,
                        'obgyne' => $row->obgyne,
                        'ortho' => $row->ortho,
                        'pedia' => $row->pedia,
                        'ent' => $row->ent,
                        'total_consumption' => $row->total_consumption,
                        'total_cons_estimated_cost' => $row->total_cons_estimated_cost,
                        'transferred_qty' => $row->transferred_qty,
                        'ending_balance' => $row->ending_balance,
                        'wardcode' => $row->wardcode,
                    ]);
                }
            }
        }

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
                // $combinedReports[$key]->from_csr += $e->from_csr + $e->total_consumption;
                $combinedReports[$key]->from_ward += $e->from_ward;
                // total stocks
                $combinedReports[$key]->total_beg_bal += $e->from_csr + $e->from_ward;
                $combinedReports[$key]->surgery += $e->surgery;
                $combinedReports[$key]->obgyne += $e->obgyne;
                $combinedReports[$key]->ortho += $e->ortho;
                $combinedReports[$key]->pedia += $e->pedia;
                $combinedReports[$key]->optha += $e->optha;
                $combinedReports[$key]->ent += $e->ent;
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
                    // 'from_csr' => $e->from_csr + $e->total_consumption,
                    'from_csr' => $e->from_csr,
                    'from_ward' => $e->from_ward,
                    'total_beg_bal' => $e->from_csr + $e->from_ward,
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
