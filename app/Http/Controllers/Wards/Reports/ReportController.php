<?php

namespace App\Http\Controllers\Wards\Reports;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class ReportController extends Controller
{

    public function index(Request $request)
    {
        //   check session
        $hasSession = Sessions::where('id', Session::getId())->exists();
        $dateRange = $request->date;
        $from = null;
        $to = null;
        $locationStockBalance = null;
        $now = Carbon::now();

        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        if ($hasSession) {
            $user = Auth::user();

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

            Sessions::where('id', Session::getId())->update([
                'location' => $authCode,
            ]);
        }
        // end check session

        $reports = array();

        $stockBalDates = DB::select(
            "SELECT beg_bal_created_at AS beg_bal_date, end_bal_created_at AS end_bal_date
            FROM csrw_stock_bal_date_logs
            WHERE wardcode = '$authCode'
            oRDER BY created_at DESC;"
        );
        $default_from = $stockBalDates[0]->beg_bal_date;
        $default_to = $stockBalDates[0]->end_bal_date;

        preg_match('/\[\s*(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+)\s*\] - \[\s*(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d+|ONGOING)\s*\]/', $dateRange, $matches);
        if ($matches) {
            $from = $matches[1]; // "2025-02-24 11:42:52.846"
            $to = $matches[2] === 'ONGOING' ? Carbon::now() : $matches[2]; // "2025-02-24 11:43:41.783" or null if "ONGOING"
        }

        //#region NOTES
        // WHEN THey ASK WHY RECEIVED FROM CSR/WARDS IN NOT SHOWING IN THE CURRENT
        // REPORT: IT'S BECAUSE ENDING BALANCE IS NOT YET DECLARED YET
        //#endregion
        // // NEW
        if ($from == null) {
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
                    FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb AND (cl.created_at BETWEEN '$default_from' AND '$default_to')) as 'surgery',
                    (SELECT SUM(CASE WHEN tscode = 'GYNE' THEN quantity ELSE 0 END)
                    FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb AND (cl.created_at BETWEEN '$default_from' AND '$default_to')) as 'obgyne',
                    (SELECT SUM(CASE WHEN tscode = 'ORTHO' THEN quantity ELSE 0 END)
                    FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb AND (cl.created_at BETWEEN '$default_from' AND '$default_to')) as 'ortho',
                    (SELECT SUM(CASE WHEN tscode = 'PEDIA' THEN quantity ELSE 0 END)
                    FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb AND (cl.created_at BETWEEN '$default_from' AND '$default_to')) as 'pedia',
                    (SELECT SUM(CASE WHEN tscode = 'OPHTH' THEN quantity ELSE 0 END)
                    FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb AND (cl.created_at BETWEEN '$default_from' AND '$default_to')) as 'optha',
                    (SELECT SUM(CASE WHEN tscode = 'ENT' THEN quantity ELSE 0 END)
                    FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb AND (cl.created_at BETWEEN '$default_from' AND '$default_to')) as 'ent',
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
                    WHERE pcchrgdte BETWEEN '$default_from' AND '$default_to'
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
                        (csrw_location_stock_balance.beg_bal_created_at BETWEEN '$default_from' AND '$default_to')
                        OR csrw_location_stock_balance.beg_bal_created_at IS NULL
                    )
                    AND (
                        (csrw_location_stock_balance.end_bal_created_at BETWEEN '$default_from' AND '$default_to')
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
        } else {
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
        }
        // dd($ward_report);

        // // new
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
                    'actual_inventory' => 0
                ];
            }
        }
        // Convert the combined associative array into a regular array of objects
        $reports = array_values($combinedReports);
        // dd($loopCount);

        return Inertia::render('Wards/Reports/Consumption/Index', [
            'reports' => $reports,
            'locationStockBalance' => $locationStockBalance,
            'stockBalDates' => $stockBalDates,
        ]);

        // // maintenance page
        // return Inertia::render('UnderMaintenancePage', [
        //     'reports' => $reports
        // ]);
    }
}
