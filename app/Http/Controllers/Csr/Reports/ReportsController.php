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
                    csrw_wards_stocks.wards_quantity
                    FROM csrw_csr_stocks
                    JOIN hclass2 ON csrw_csr_stocks.cl2comb = hclass2.cl2comb
                    LEFT JOIN (
                        SELECT ward.cl2comb, SUM(ward.quantity) as wards_quantity
                        FROM csrw_wards_stocks as ward
                        GROUP BY ward.cl2comb
                    ) csrw_wards_stocks ON csrw_csr_stocks.cl2comb = csrw_wards_stocks.cl2comb
                    LEFT JOIN huom ON csrw_csr_stocks.uomcode = huom.uomcode
                    GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_wards_stocks.wards_quantity
                    ORDER BY hclass2.cl2desc ASC;"
        );

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
                'supplies_issued_to_wards_quantity' => $e->wards_quantity,
                'supplies_issued_to_wards_total_cost' => $e->wards_quantity * $e->selling_price,
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
                    $r->consumption_quantity = 0;
                    $r->consumption_total_cost = 0;
                }
            }
        }

        return Inertia::render('Csr/Reports/Index', [
            'reports' => $reports
        ]);
    }
}
