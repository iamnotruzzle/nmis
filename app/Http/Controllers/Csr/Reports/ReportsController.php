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

        $csr_report = DB::select(
            "SELECT
                csr_stock.csr_stock_id,
                item.cl2comb,
                item.cl2desc AS item_description,
                uom.uomdesc AS unit,
                price.price_per_unit AS unit_cost,
                csr_stock.quantity_after AS beg_bal_csr_quantity,
                (csr_stock.quantity_after + csr_stock.total_issued_qty) AS received_mms_qty,
                (csr_stock.total_issued_qty + csr_stock.quantity_after) * price.price_per_unit AS received_mms_total_cost,
                csr_stock.total_issued_qty as issued_qty,
                (csr_stock.total_issued_qty * price.price_per_unit) AS issued_total_cost,
                pat_charge.quantity AS consump_quantity,
                pat_charge.price_total AS consump_total_cost,
                csr_stock.created_at
            FROM
                csrw_csr_item_conversion AS csr_stock
            JOIN hclass2 AS item ON item.cl2comb = csr_stock.cl2comb_after
            JOIN csrw_item_prices AS price ON price.item_conversion_id = csr_stock.csr_stock_id
            JOIN huom AS uom ON uom.uomcode = item.uomcode
            LEFT JOIN csrw_wards_stocks AS ward_stock ON ward_stock.stock_id = csr_stock.csr_stock_id
            LEFT JOIN csrw_patient_charge_logs AS pat_charge ON pat_charge.ward_stocks_id = ward_stock.id
            WHERE
                (CAST(csr_stock.created_at AS DATE) BETWEEN '2024-11-04' AND '2024-11-30')"
        );
        // dd($csr_report);

        $ward_report = DB::select(
            "SELECT
                ward.stock_id AS csr_stock_id,
                SUM(csrw_location_stock_balance.beginning_balance) AS beginning_balance,
                SUM(csrw_location_stock_balance.ending_balance) AS ending_balance
            FROM
                csrw_wards_stocks AS ward
            JOIN hclass2 ON ward.cl2comb = hclass2.cl2comb
            JOIN csrw_item_prices ON csrw_item_prices.ris_no = ward.ris_no
            LEFT JOIN huom ON ward.uomcode = huom.uomcode
            LEFT JOIN (
                SELECT ward_stocks_id, SUM(quantity) AS charge_quantity
                FROM csrw_patient_charge_logs
                WHERE CAST(pcchrgdte AS DATE) BETWEEN '2024-11-04' AND '2024-11-30'
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
                (CAST(csrw_location_stock_balance.beg_bal_created_at AS DATE) BETWEEN '2024-11-04' AND '2024-11-30')
                OR csrw_location_stock_balance.beg_bal_created_at IS NULL
                )
                AND (
                    (CAST(csrw_location_stock_balance.end_bal_created_at AS DATE) BETWEEN '2024-11-04' AND '2024-11-30')
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
                    'beginning_balance' => $row->beginning_balance ?? 0,
                    'ending_balance' => $row->ending_balance ?? 0
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
                        'beginning_balance' => 0,
                        'ending_balance' => 0
                    ]
                );
            }
        }

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
                    'beginning_balance' => $record['beginning_balance'],
                    'ending_balance' => $record['ending_balance'],
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
                $aggregatedResults[$key]['beginning_balance'] += $record['beginning_balance'];
                $aggregatedResults[$key]['ending_balance'] += $record['ending_balance'];
            }
        }
        dd($aggregatedResults);

        // if (is_null($request->from) || is_null($request->to)) {
        //     // WHERE created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
        //     // the where above means between the first day and last day of this month
        //     $csr_report = DB::select(
        //         "SELECT hclass2.cl2comb,
        //         hclass2.cl2desc,
        //         huom.uomdesc,
        //         clsb_csr.beginning_balance as csr_beginning_balance,
        //         clsb_csr.ending_balance as csr_ending_balance,
        //         clsb_ward.beginning_balance as ward_beginning_balance,
        //         clsb_ward.ending_balance as ward_ending_balance,
        //         (SELECT TOP 1 price_per_unit FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'price_per_unit',
        //         SUM(csrw_csr_stocks.quantity) as csr_quantity,
        //         csrw_wards_stocks.wards_quantity,
        //         csrw_wards_stocks.converted_quantity as converted_quantity,
        //         csrw_patient_charge_logs.charge_quantity as consumption_quantity,
        //         csrw_patient_charge_logs.charge_total as consumption_total_cost
        //         FROM csrw_csr_stocks
        //         JOIN hclass2 ON csrw_csr_stocks.cl2comb = hclass2.cl2comb
        //         LEFT JOIN (
        //             SELECT ward.cl2comb, SUM(ward.quantity) as wards_quantity, SUM(ward.converted_quantity) as converted_quantity
        //             FROM csrw_wards_stocks as ward
        //             WHERE ward.[from] = 'CSR'
        //             GROUP BY ward.cl2comb
        //         ) csrw_wards_stocks ON hclass2.cl2comb = csrw_wards_stocks.cl2comb
        //         LEFT JOIN (
        //             SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
        //             FROM csrw_patient_charge_logs as charge
        //             WHERE charge.[from] = 'CSR'
        //             GROUP BY charge.itemcode
        //         ) csrw_patient_charge_logs ON csrw_csr_stocks.cl2comb = csrw_patient_charge_logs.itemcode
        //         LEFT JOIN huom ON csrw_csr_stocks.uomcode = huom.uomcode
        //         RIGHT JOIN (
        //             SELECT id, cl2comb, ending_balance, beginning_balance
        //             FROM csrw_location_stock_balance
        //             WHERE location = 'CSR'
        //         ) AS clsb_csr ON hclass2.cl2comb = clsb_csr.cl2comb
        //         LEFT JOIN (
        //             SELECT cl2comb, SUM(ending_balance) as ending_balance, SUM(beginning_balance) as beginning_balance
        //             FROM csrw_location_stock_balance
        //             WHERE location != 'CSR'
        //             GROUP BY cl2comb
        //         ) AS clsb_ward ON hclass2.cl2comb = clsb_ward.cl2comb
        //         WHERE created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
        //         GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_wards_stocks.wards_quantity, csrw_patient_charge_logs.charge_quantity, csrw_patient_charge_logs.charge_total,
        //         clsb_csr.beginning_balance, clsb_csr.ending_balance, clsb_ward.beginning_balance, clsb_ward.ending_balance, csrw_wards_stocks.converted_quantity
        //         ORDER BY hclass2.cl2desc ASC;"
        //     );
        //     // dd($csr_report);
        // } else {
        //     $csr_report = DB::select(
        //         "SELECT hclass2.cl2comb,
        //         hclass2.cl2desc,
        //         huom.uomdesc,
        //         clsb_csr.beginning_balance as csr_beginning_balance,
        //         clsb_csr.ending_balance as csr_ending_balance,
        //         clsb_ward.beginning_balance as ward_beginning_balance,
        //         clsb_ward.ending_balance as ward_ending_balance,
        //         (SELECT TOP 1 price_per_unit FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'price_per_unit',
        //         SUM(csrw_csr_stocks.quantity) as csr_quantity,
        //         csrw_wards_stocks.wards_quantity,
        //         csrw_wards_stocks.converted_quantity as converted_quantity,
        //         csrw_patient_charge_logs.charge_quantity as consumption_quantity,
        //         csrw_patient_charge_logs.charge_total as consumption_total_cost
        //         FROM csrw_csr_stocks
        //         JOIN hclass2 ON csrw_csr_stocks.cl2comb = hclass2.cl2comb
        //         LEFT JOIN (
        //             SELECT ward.cl2comb, SUM(ward.quantity) as wards_quantity, SUM(ward.converted_quantity) as converted_quantity
        //             FROM csrw_wards_stocks as ward
        //             WHERE ward.[from] = 'CSR'
        //             GROUP BY ward.cl2comb
        //         ) csrw_wards_stocks ON hclass2.cl2comb = csrw_wards_stocks.cl2comb
        //         LEFT JOIN (
        //             SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
        //             FROM csrw_patient_charge_logs as charge
        //             WHERE charge.[from] = 'CSR'
        //             GROUP BY charge.itemcode
        //         ) csrw_patient_charge_logs ON csrw_csr_stocks.cl2comb = csrw_patient_charge_logs.itemcode
        //         LEFT JOIN huom ON csrw_csr_stocks.uomcode = huom.uomcode
        //         RIGHT JOIN (
        //             SELECT id, cl2comb, ending_balance, beginning_balance
        //             FROM csrw_location_stock_balance
        //             WHERE location = 'CSR'
        //         ) AS clsb_csr ON hclass2.cl2comb = clsb_csr.cl2comb
        //         LEFT JOIN (
        //             SELECT cl2comb, SUM(ending_balance) as ending_balance, SUM(beginning_balance) as beginning_balance
        //             FROM csrw_location_stock_balance
        //             WHERE location != 'CSR'
        //             GROUP BY cl2comb
        //         ) AS clsb_ward ON hclass2.cl2comb = clsb_ward.cl2comb
        //         WHERE created_at BETWEEN '$from' AND '$to'
        //         GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_wards_stocks.wards_quantity, csrw_patient_charge_logs.charge_quantity, csrw_patient_charge_logs.charge_total,
        //         clsb_csr.beginning_balance, clsb_csr.ending_balance, clsb_ward.beginning_balance, clsb_ward.ending_balance, csrw_wards_stocks.converted_quantity
        //         ORDER BY hclass2.cl2desc ASC;"
        //     );
        // }
        // // dd($csr_report);

        // foreach ($csr_report as $e) {
        //     $reports[] = (object) [
        //         'item_description' => $e->cl2desc,
        //         'unit' => $e->uomdesc,
        //         'unit_cost' => $e->price_per_unit,
        //         'csr_quantity' => $e->csr_beginning_balance, // csr starting balance
        //         'csr_total_cost' => $e->csr_beginning_balance * $e->price_per_unit,
        //         'ward_quantity' => $e->ward_beginning_balance, // ward starting balance
        //         'ward_total_cost' => $e->ward_beginning_balance * $e->price_per_unit,
        //         'total_beg_total_quantity' => $e->csr_beginning_balance + $e->ward_beginning_balance,
        //         'total_beg_total_cost' => ($e->csr_beginning_balance + $e->ward_beginning_balance) * $e->price_per_unit,
        //         'supplies_issued_to_wards_quantity' => $e->wards_quantity + $e->consumption_quantity + $e->converted_quantity, // + converted quantity
        //         'supplies_issued_to_wards_total_cost' => ($e->wards_quantity + $e->consumption_quantity) * $e->price_per_unit,
        //         'consumption_quantity' => $e->consumption_quantity,
        //         'consumption_total_cost' => $e->consumption_total_cost,
        //         'csr_quantity_ending_bal' => $e->csr_ending_balance, // csr ending balance
        //         'csr_total_cost_ending_bal' => $e->csr_ending_balance * $e->price_per_unit,
        //         'ward_quantity_ending_bal' => $e->ward_ending_balance,
        //         'ward_total_cost_ending_bal' => $e->ward_ending_balance * $e->price_per_unit, // ward ending balance
        //         'total_end_total_quantity' => $e->csr_ending_balance + $e->ward_ending_balance,
        //         'total_end_total_cost' => ($e->csr_ending_balance + $e->ward_ending_balance) * $e->price_per_unit,
        //     ];
        // }
        // dd($reports);

        return Inertia::render('Csr/Reports/Index', [
            // 'reports' => $reports
        ]);

        // return Inertia::render('UnderMaintenancePage', [
        //     // 'reports' => $reports
        // ]);
    }
}
