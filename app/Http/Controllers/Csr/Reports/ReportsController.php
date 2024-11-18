<?php

namespace App\Http\Controllers\Csr\Reports;

use App\Http\Controllers\Controller;
use App\Models\PatientChargeLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $reports = array();

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        // $csr_report = DB::select(
        //     "SELECT
        //         csr_stock.csr_stock_id,
        //         item.cl2comb,
        //         item.cl2desc AS item_description,
        //         uom.uomdesc AS unit,
        //         price.price_per_unit AS unit_cost,
        //         (csr_stock.total_issued_qty + csr_stock.quantity_after) AS beg_bal_csr_quantity,
        //         (csr_stock.total_issued_qty + csr_stock.quantity_after) AS received_mms_qty,
        //         (csr_stock.total_issued_qty + csr_stock.quantity_after) * price.price_per_unit AS received_mms_total_cost,
        //         csr_stock.total_issued_qty as issued_qty,
        //         (csr_stock.total_issued_qty * price.price_per_unit) AS issued_total_cost, --
        //         pat_charge.quantity AS consump_quantity,
        //         pat_charge.price_total AS consump_total_cost,
        //         csr_stock.created_at,
        //         csr_stock.quantity_after AS end_bal_csr_quantity
        //     FROM
        //         csrw_csr_item_conversion AS csr_stock
        //     JOIN hclass2 AS item ON item.cl2comb = csr_stock.cl2comb_after
        //     JOIN csrw_item_prices AS price ON price.item_conversion_id = csr_stock.csr_stock_id
        //     JOIN huom AS uom ON uom.uomcode = item.uomcode
        //     LEFT JOIN csrw_wards_stocks AS ward_stock ON ward_stock.stock_id = csr_stock.csr_stock_id
        //     LEFT JOIN csrw_patient_charge_logs AS pat_charge ON pat_charge.ward_stocks_id = ward_stock.id
        //     WHERE
        //         (CAST(csr_stock.created_at AS DATE) BETWEEN '2024-11-04' AND '2024-11-30';"
        // );
        $csr_report = DB::select(
            "SELECT
                csr_stock.csr_stock_id,
                item.cl2comb,
                item.cl2desc AS item_description,
                uom.uomdesc AS unit,
                price.price_per_unit AS unit_cost,
                (csr_stock.total_issued_qty + csr_stock.quantity_after) AS beg_bal_csr_quantity,
                (csr_stock.total_issued_qty + csr_stock.quantity_after) AS received_mms_qty,
                (csr_stock.total_issued_qty + csr_stock.quantity_after) * price.price_per_unit AS received_mms_total_cost,
                csr_stock.total_issued_qty as issued_qty,
                (csr_stock.total_issued_qty * price.price_per_unit) AS issued_total_cost, --
                pat_charge.quantity AS consump_quantity,
                pat_charge.price_total AS consump_total_cost,
                csr_stock.created_at,
                csr_stock.quantity_after AS end_bal_csr_quantity
            FROM
                csrw_csr_item_conversion AS csr_stock
            JOIN hclass2 AS item ON item.cl2comb = csr_stock.cl2comb_after
            JOIN csrw_item_prices AS price ON price.item_conversion_id = csr_stock.csr_stock_id
            JOIN huom AS uom ON uom.uomcode = item.uomcode
            LEFT JOIN csrw_wards_stocks AS ward_stock ON ward_stock.stock_id = csr_stock.csr_stock_id
            LEFT JOIN csrw_patient_charge_logs AS pat_charge ON pat_charge.ward_stocks_id = ward_stock.id;"
        );
        // dd($csr_report);

        $ward_report = DB::select(
            "SELECT
                ward.stock_id AS csr_stock_id,
                SUM(csrw_location_stock_balance.beginning_balance) AS beg_bal_ward_quantity,
                SUM(csrw_location_stock_balance.ending_balance) AS end_bal_ward_quantity
            FROM
                csrw_wards_stocks AS ward
            JOIN hclass2 ON ward.cl2comb = hclass2.cl2comb
            JOIN csrw_item_prices ON csrw_item_prices.ris_no = ward.ris_no
            LEFT JOIN huom ON ward.uomcode = huom.uomcode
            LEFT JOIN (
                SELECT ward_stocks_id, SUM(quantity) AS charge_quantity
                FROM csrw_patient_charge_logs
                WHERE CAST(pcchrgdte AS DATE) BETWEEN '$from' AND '$to'
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
                ward.is_consumable IS NULL
                AND (
                (CAST(csrw_location_stock_balance.beg_bal_created_at AS DATE) BETWEEN '$from' AND '$to')
                OR csrw_location_stock_balance.beg_bal_created_at IS NULL
                )
                AND (
                    (CAST(csrw_location_stock_balance.end_bal_created_at AS DATE) BETWEEN '$from' AND '$to')
                    OR csrw_location_stock_balance.end_bal_created_at IS NULL
                )
            GROUP BY
                hclass2.cl2comb,
                hclass2.cl2desc,
                huom.uomdesc,
                csrw_item_prices.price_per_unit,
                ward.ris_no,
                csrw_patient_charge_logs.charge_quantity,
                ward.stock_id
            ORDER BY
                hclass2.cl2desc ASC;"
        );
        // dd($ward_report);

        // Step 1: Index second query results by `csr_stock_id`
        $combinedResults = [];

        // Index the second result set by csr_stock_id
        foreach ($ward_report as $row) {
            $combinedResults[$row->csr_stock_id] = array_merge(
                (array)$row,
                [
                    'beg_bal_ward_quantity' => $row->beg_bal_ward_quantity ?? 0,
                    'end_bal_ward_quantity' => $row->end_bal_ward_quantity ?? 0
                ]
            );
        }

        // Step 2: Merge the first result set with the second
        foreach ($csr_report as $row) {
            $csr_stock_id = $row->csr_stock_id;

            if (isset($combinedResults[$csr_stock_id])) {
                $combinedResults[$csr_stock_id] = array_merge(
                    $combinedResults[$csr_stock_id],
                    (array)$row
                );
            } else {
                $combinedResults[$csr_stock_id] = array_merge(
                    (array)$row,
                    [
                        'beg_bal_ward_quantity' => 0,
                        'end_bal_ward_quantity' => 0
                    ]
                );
            }
        }
        // dd($combinedResults);

        // Step 3: Aggregate results by `cl2comb` and `unit_cost`, removing `csr_stock_id` and `created_at`
        $aggregatedResults = [];
        foreach ($combinedResults as $record) {
            $key = $record['cl2comb'] . '-' . $record['unit_cost'];

            if (!isset($aggregatedResults[$key])) {
                // Initialize the entry if it doesn't exist
                $aggregatedResults[$key] = [
                    'cl2comb' => $record['cl2comb'],
                    'item_description' => $record['item_description'],
                    'unit' => $record['unit'],
                    'unit_cost' => $record['unit_cost'],
                    'beg_bal_csr_quantity' => $record['beg_bal_csr_quantity'] ?? 0,
                    'received_mms_qty' => $record['received_mms_qty'] ?? 0,
                    'received_mms_total_cost' => $record['received_mms_total_cost'] ?? 0,
                    'issued_qty' => $record['issued_qty'] ?? 0,
                    'issued_total_cost' => $record['issued_total_cost'] ?? 0,
                    'consump_quantity' => $record['consump_quantity'] ?? 0,
                    'consump_total_cost' => $record['consump_total_cost'] ?? 0,
                    'beg_bal_ward_quantity' => $record['beg_bal_ward_quantity'],
                    'end_bal_ward_quantity' => $record['end_bal_ward_quantity'],
                    'end_bal_csr_quantity' => $record['end_bal_csr_quantity'] ?? 0,
                    'beg_bal_total_quantity' => ($record['beg_bal_ward_quantity'] ?? 0) + ($record['beg_bal_csr_quantity'] ?? 0), // New calculation
                    'end_bal_total_quantity' => ($record['end_bal_ward_quantity'] ?? 0) + ($record['end_bal_csr_quantity'] ?? 0), // New calculation
                    'beg_bal_total_cost' => (($record['beg_bal_ward_quantity'] ?? 0) + ($record['beg_bal_csr_quantity'] ?? 0)) * ($record['unit_cost'] ?? 0), // New calculation
                    'end_bal_total_cost' => (($record['end_bal_ward_quantity'] ?? 0) + ($record['end_bal_csr_quantity'] ?? 0)) * ($record['unit_cost'] ?? 0),
                ];
            } else {
                // Sum the values for the existing entry
                $aggregatedResults[$key]['beg_bal_csr_quantity'] += $record['beg_bal_csr_quantity'] ?? 0;
                $aggregatedResults[$key]['received_mms_qty'] += $record['received_mms_qty'] ?? 0;
                $aggregatedResults[$key]['received_mms_total_cost'] += $record['received_mms_total_cost'] ?? 0;
                $aggregatedResults[$key]['issued_qty'] += $record['issued_qty'] ?? 0;
                $aggregatedResults[$key]['issued_total_cost'] += $record['issued_total_cost'] ?? 0;
                $aggregatedResults[$key]['consump_quantity'] += $record['consump_quantity'] ?? 0;
                $aggregatedResults[$key]['consump_total_cost'] += $record['consump_total_cost'] ?? 0;
                $aggregatedResults[$key]['beg_bal_ward_quantity'] += $record['beg_bal_ward_quantity'];
                $aggregatedResults[$key]['end_bal_ward_quantity'] += $record['end_bal_ward_quantity'];
                $aggregatedResults[$key]['end_bal_csr_quantity'] += $record['end_bal_csr_quantity'] ?? 0;

                // Update totals
                $aggregatedResults[$key]['beg_bal_total_quantity'] += ($record['beg_bal_ward_quantity'] ?? 0) + ($record['beg_bal_csr_quantity'] ?? 0);
                $aggregatedResults[$key]['end_bal_total_quantity'] += ($record['end_bal_ward_quantity'] ?? 0) + ($record['end_bal_csr_quantity'] ?? 0);
                $aggregatedResults[$key]['beg_bal_total_cost'] = $aggregatedResults[$key]['beg_bal_total_quantity'] * ($record['unit_cost'] ?? 0); // Update beg_bal_total_cost
                $aggregatedResults[$key]['end_bal_total_cost'] = $aggregatedResults[$key]['end_bal_total_quantity'] * ($record['unit_cost'] ?? 0); // Update end_bal_total_cost
            }
        }
        // dd($aggregatedResults);

        $beg_bal_dates = DB::select(
            "SELECT CAST(beg_bal_created_at as DATE) AS date
                FROM csrw_stock_bal_date_logs
                ORDER BY created_at DESC;"
        );
        // dd($beg_bal_dates);

        $end_bal_dates = DB::select(
            "SELECT CAST(end_bal_created_at AS DATE) AS date
                FROM csrw_stock_bal_date_logs
                WHERE end_bal_created_at IS NOT NULL
                ORDER BY created_at DESC;
                "
        );

        // Remove keys and re-index the array
        $aggregatedResults = array_values($aggregatedResults);

        return Inertia::render('Csr/Reports/Index', [
            'reports' => $aggregatedResults,
            'beg_bal_dates' => $beg_bal_dates,
            'end_bal_dates' => $end_bal_dates
        ]);

        // return Inertia::render('UnderMaintenancePage', [
        //     // 'reports' => $reports
        // ]);
    }
}
