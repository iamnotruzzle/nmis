<?php

namespace App\Http\Controllers\Reports\Csr;

use App\Exports\CsrStocksMedicalSuppliesReport;
use App\Http\Controllers\Controller;
use App\Models\CsrStocksMedicalSupplies;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CsrStocksMedicalSuppliesReportController extends Controller
{
    public function export(Request $request)
    {
        $reports = array();

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        if (is_null($request->from) || is_null($request->to)) {
            // WHERE created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
            // the where above means between the first day and last day of this month
            $csr_report = DB::select(
                "SELECT hclass2.cl2comb,
                hclass2.cl2desc,
                huom.uomdesc,
                clsb_csr.beginning_balance as csr_beginning_balance,
                clsb_csr.ending_balance as csr_ending_balance,
                clsb_ward.beginning_balance as ward_beginning_balance,
                clsb_ward.ending_balance as ward_ending_balance,
                (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'selling_price',
                SUM(csrw_csr_stocks.quantity) as csr_quantity,
                csrw_wards_stocks_med_supp.wards_quantity,
                csrw_patient_charge_logs.charge_quantity as consumption_quantity,
                csrw_patient_charge_logs.charge_total as consumption_total_cost
                FROM csrw_csr_stocks
                JOIN hclass2 ON csrw_csr_stocks.cl2comb = hclass2.cl2comb
                LEFT JOIN (
                    SELECT ward.cl2comb, SUM(ward.quantity) as wards_quantity
                    FROM csrw_wards_stocks_med_supp as ward
                    WHERE ward.[from] = 'CSR'
                    GROUP BY ward.cl2comb
                ) csrw_wards_stocks_med_supp ON hclass2.cl2comb = csrw_wards_stocks_med_supp.cl2comb
                LEFT JOIN (
                    SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
                    FROM csrw_patient_charge_logs as charge
                    WHERE charge.[from] = 'CSR'
                    GROUP BY charge.itemcode
                ) csrw_patient_charge_logs ON csrw_csr_stocks.cl2comb = csrw_patient_charge_logs.itemcode
                LEFT JOIN huom ON csrw_csr_stocks.uomcode = huom.uomcode
                RIGHT JOIN (
                    SELECT id, cl2comb, ending_balance, beginning_balance
                    FROM csrw_location_stock_balance
                    WHERE location = 'CSR'
                ) AS clsb_csr ON hclass2.cl2comb = clsb_csr.cl2comb
                LEFT JOIN (
                    SELECT cl2comb, SUM(ending_balance) as ending_balance, SUM(beginning_balance) as beginning_balance
                    FROM csrw_location_stock_balance
                    WHERE location != 'CSR'
                    GROUP BY cl2comb
                ) AS clsb_ward ON hclass2.cl2comb = clsb_ward.cl2comb
                WHERE created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
                GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_wards_stocks_med_supp.wards_quantity, csrw_patient_charge_logs.charge_quantity, csrw_patient_charge_logs.charge_total,
                clsb_csr.beginning_balance, clsb_csr.ending_balance, clsb_ward.beginning_balance, clsb_ward.ending_balance
                ORDER BY hclass2.cl2desc ASC;"
            );
        } else {
            $csr_report = DB::select(
                "SELECT hclass2.cl2comb,
                hclass2.cl2desc,
                huom.uomdesc,
                clsb_csr.beginning_balance as csr_beginning_balance,
                clsb_csr.ending_balance as csr_ending_balance,
                clsb_ward.beginning_balance as ward_beginning_balance,
                clsb_ward.ending_balance as ward_ending_balance,
                (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'selling_price',
                SUM(csrw_csr_stocks.quantity) as csr_quantity,
                csrw_wards_stocks_med_supp.wards_quantity,
                csrw_patient_charge_logs.charge_quantity as consumption_quantity,
                csrw_patient_charge_logs.charge_total as consumption_total_cost
                FROM csrw_csr_stocks
                JOIN hclass2 ON csrw_csr_stocks.cl2comb = hclass2.cl2comb
                LEFT JOIN (
                    SELECT ward.cl2comb, SUM(ward.quantity) as wards_quantity
                    FROM csrw_wards_stocks_med_supp as ward
                    WHERE ward.[from] = 'CSR'
                    GROUP BY ward.cl2comb
                ) csrw_wards_stocks_med_supp ON hclass2.cl2comb = csrw_wards_stocks_med_supp.cl2comb
                LEFT JOIN (
                    SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
                    FROM csrw_patient_charge_logs as charge
                    WHERE charge.[from] = 'CSR'
                    GROUP BY charge.itemcode
                ) csrw_patient_charge_logs ON csrw_csr_stocks.cl2comb = csrw_patient_charge_logs.itemcode
                LEFT JOIN huom ON csrw_csr_stocks.uomcode = huom.uomcode
                RIGHT JOIN (
                    SELECT id, cl2comb, ending_balance, beginning_balance
                    FROM csrw_location_stock_balance
                    WHERE location = 'CSR'
                ) AS clsb_csr ON hclass2.cl2comb = clsb_csr.cl2comb
                LEFT JOIN (
                    SELECT cl2comb, SUM(ending_balance) as ending_balance, SUM(beginning_balance) as beginning_balance
                    FROM csrw_location_stock_balance
                    WHERE location != 'CSR'
                    GROUP BY cl2comb
                ) AS clsb_ward ON hclass2.cl2comb = clsb_ward.cl2comb
                WHERE created_at BETWEEN '$from' AND '$to'
                GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_wards_stocks_med_supp.wards_quantity, csrw_patient_charge_logs.charge_quantity, csrw_patient_charge_logs.charge_total,
                clsb_csr.beginning_balance, clsb_csr.ending_balance, clsb_ward.beginning_balance, clsb_ward.ending_balance
                ORDER BY hclass2.cl2desc ASC;"
            );
        }
        // dd($csr_report);

        foreach ($csr_report as $e) {
            $reports[] = (object) [
                'item_description' => $e->cl2desc,
                'unit' => $e->uomdesc,
                'unit_cost' => $e->selling_price,
                'csr_quantity' => $e->csr_beginning_balance, // csr starting balance
                'csr_total_cost' => $e->csr_beginning_balance * $e->selling_price,
                'ward_quantity' => $e->ward_beginning_balance, // ward starting balance
                'ward_total_cost' => $e->ward_beginning_balance * $e->selling_price,
                'total_beg_total_quantity' => $e->csr_beginning_balance + $e->ward_beginning_balance,
                'total_beg_total_cost' => ($e->csr_beginning_balance + $e->ward_beginning_balance) * $e->selling_price,
                'supplies_issued_to_wards_quantity' => $e->wards_quantity + $e->consumption_quantity,
                'supplies_issued_to_wards_total_cost' => ($e->wards_quantity + $e->consumption_quantity) * $e->selling_price,
                'consumption_quantity' => $e->consumption_quantity,
                'consumption_total_cost' => $e->consumption_total_cost,
                'csr_quantity_ending_bal' => $e->csr_ending_balance, // csr ending balance
                'csr_total_cost_ending_bal' => $e->csr_ending_balance * $e->selling_price,
                'ward_quantity_ending_bal' => $e->ward_ending_balance,
                'ward_total_cost_ending_bal' => $e->ward_ending_balance * $e->selling_price, // ward ending balance
                'total_end_total_quantity' => $e->csr_ending_balance + $e->ward_ending_balance,
                'total_end_total_cost' => ($e->csr_ending_balance + $e->ward_ending_balance) * $e->selling_price,
            ];
        }
        // dd($reports);

        return Excel::download(new CsrStocksMedicalSuppliesReport($reports), 'report.xlsx');
    }
}
