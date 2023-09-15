<?php

namespace App\Http\Controllers\Reports\Csr;

use App\Exports\CsrStocksReport;
use App\Http\Controllers\Controller;
use App\Models\CsrStocks;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CsrStocksReportController extends Controller
{
    public function export(Request $request)
    {
        $reports = array();

        if (is_null($request->from) || is_null($request->to)) {
            $csr_report = DB::select(
                "SELECT hclass2.cl2comb,
                hclass2.cl2desc,
                huom.uomdesc,
                (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'selling_price',
                SUM(csrw_csr_stocks.quantity) as csr_quantity,
                csrw_wards_stocks.wards_quantity,
                csrw_patient_charge_logs.charge_quantity as consumption_quantity,
                csrw_patient_charge_logs.charge_total as consumption_total_cost
                FROM csrw_csr_stocks
                JOIN hclass2 ON csrw_csr_stocks.cl2comb = hclass2.cl2comb
                LEFT JOIN (
                    SELECT ward.cl2comb, SUM(ward.quantity) as wards_quantity
                    FROM csrw_wards_stocks as ward
                    WHERE ward.[from] = 'CSR'
                    GROUP BY ward.cl2comb
                ) csrw_wards_stocks ON hclass2.cl2comb = csrw_wards_stocks.cl2comb
                LEFT JOIN (
                    SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
                    FROM csrw_patient_charge_logs as charge
                    WHERE charge.[from] = 'CSR'
                    GROUP BY charge.itemcode
                ) csrw_patient_charge_logs ON csrw_csr_stocks.cl2comb = csrw_patient_charge_logs.itemcode
                LEFT JOIN huom ON csrw_csr_stocks.uomcode = huom.uomcode
                WHERE created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
                GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_wards_stocks.wards_quantity, csrw_patient_charge_logs.charge_quantity, csrw_patient_charge_logs.charge_total
                ORDER BY hclass2.cl2desc ASC;"
            );
        } else {
            $csr_report = DB::select(
                "SELECT hclass2.cl2comb,
                hclass2.cl2desc,
                huom.uomdesc,
                (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'selling_price',
                SUM(csrw_csr_stocks.quantity) as csr_quantity,
                csrw_wards_stocks.wards_quantity,
                csrw_patient_charge_logs.charge_quantity as consumption_quantity,
                csrw_patient_charge_logs.charge_total as consumption_total_cost
                FROM csrw_csr_stocks
                JOIN hclass2 ON csrw_csr_stocks.cl2comb = hclass2.cl2comb
                LEFT JOIN (
                    SELECT ward.cl2comb, SUM(ward.quantity) as wards_quantity
                    FROM csrw_wards_stocks as ward
                    WHERE ward.[from] = 'CSR'
                    GROUP BY ward.cl2comb
                ) csrw_wards_stocks ON hclass2.cl2comb = csrw_wards_stocks.cl2comb
                LEFT JOIN (
                    SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
                    FROM csrw_patient_charge_logs as charge
                    WHERE charge.[from] = 'CSR'
                    GROUP BY charge.itemcode
                ) csrw_patient_charge_logs ON csrw_csr_stocks.cl2comb = csrw_patient_charge_logs.itemcode
                LEFT JOIN huom ON csrw_csr_stocks.uomcode = huom.uomcode
                WHERE created_at BETWEEN '$request->from' AND '$request->to'
                GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_wards_stocks.wards_quantity, csrw_patient_charge_logs.charge_quantity, csrw_patient_charge_logs.charge_total
                ORDER BY hclass2.cl2desc ASC;"
            );
        }
        // dd($csr_report);

        foreach ($csr_report as $e) {
            $reports[] = (object) [
                'item_description' => $e->cl2desc,
                'unit' => $e->uomdesc,
                'unit_cost' => $e->selling_price,
                'csr_quantity' => $e->csr_quantity,
                'csr_total_cost' => $e->csr_quantity * $e->selling_price,
                'ward_quantity' => $e->wards_quantity,
                'ward_total_cost' => $e->wards_quantity * $e->selling_price,
                'total_beg_total_quantity' => $e->csr_quantity + $e->wards_quantity,
                'total_beg_total_cost' => ($e->csr_quantity + $e->wards_quantity) * $e->selling_price,
                'supplies_issued_to_wards_quantity' => $e->wards_quantity + $e->consumption_quantity,
                'supplies_issued_to_wards_total_cost' => ($e->wards_quantity + $e->consumption_quantity) * $e->selling_price,
                'consumption_quantity' => $e->consumption_quantity,
                'consumption_total_cost' => $e->consumption_total_cost,
                'csr_quantity_ending_bal' => $e->csr_quantity,
                'csr_total_cost_ending_bal' => $e->csr_quantity * $e->selling_price,
                'ward_quantity_ending_bal' => ($e->wards_quantity + $e->consumption_quantity) - $e->consumption_quantity,
                'ward_total_cost_ending_bal' => (($e->wards_quantity + $e->consumption_quantity) - $e->consumption_quantity) * $e->selling_price,
                'total_end_total_quantity' => $e->csr_quantity + ($e->wards_quantity + $e->consumption_quantity) - $e->consumption_quantity,
                'total_end_total_cost' => ($e->csr_quantity + ($e->wards_quantity + $e->consumption_quantity) - $e->consumption_quantity) * $e->selling_price,
            ];
        }

        return Excel::download(new CsrStocksReport($reports), 'report.xlsx');
    }
}
