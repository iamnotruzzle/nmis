<?php

namespace App\Http\Controllers\Csr\Reports;

use App\Http\Controllers\Controller;
use App\Models\PatientChargeLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportsController extends Controller
{
    public function index()
    {
        $reports = array();

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
                        GROUP BY ward.cl2comb
                    ) csrw_wards_stocks ON csrw_csr_stocks.cl2comb = csrw_wards_stocks.cl2comb
                    LEFT JOIN (
                        SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
                        FROM csrw_patient_charge_logs as charge
                        GROUP BY charge.itemcode
                    ) csrw_patient_charge_logs ON csrw_csr_stocks.cl2comb = csrw_patient_charge_logs.itemcode
                    LEFT JOIN huom ON csrw_csr_stocks.uomcode = huom.uomcode
                    GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_wards_stocks.wards_quantity, csrw_patient_charge_logs.charge_quantity, csrw_patient_charge_logs.charge_total
                    ORDER BY hclass2.cl2desc ASC;"
        );

        // dd($csr_report);

        foreach ($csr_report as $e) {
            $reports[] = (object) [
                'cl2comb' => $e->cl2comb,
                'item_description' => $e->cl2desc,
                'unit' => $e->uomdesc,
                'unit_cost' => $e->selling_price,
                'csr_quantity' => $e->csr_quantity,
                'ward_quantity' => $e->wards_quantity,
                'total_beg_total_quantity' => $e->csr_quantity + $e->wards_quantity,
                'total_beg_total_cost' => ($e->csr_quantity + $e->wards_quantity) * $e->selling_price,
                'supplies_issued_to_wards_quantity' => $e->wards_quantity + $e->consumption_quantity,
                'supplies_issued_to_wards_total_cost' => ($e->wards_quantity + $e->consumption_quantity) * $e->selling_price,
                'consumption_quantity' => $e->consumption_quantity,
                'consumption_total_cost' => $e->consumption_total_cost
            ];
        }

        $patient_charge_logs =
            DB::table('csrw_patient_charge_logs')
            ->select('itemcode', DB::raw('SUM(quantity) as quantity'))
            ->groupBy('itemcode')
            ->get();
        // dd($patient_charge_logs);

        foreach ($reports as $r) {
            foreach ($patient_charge_logs as $pcl) {
                if ($pcl->itemcode == $r->cl2comb) {
                    $r->consumption_quantity = $pcl->quantity;
                    $r->consumption_total_cost = $r->unit_cost * $pcl->quantity;
                } else {
                    $r->consumption_quantity = null;
                    $r->consumption_total_cost = null;
                }
            }
        }

        return Inertia::render('Csr/Reports/Index', [
            'reports' => $reports
        ]);
    }
}