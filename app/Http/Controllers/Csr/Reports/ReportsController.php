<?php

namespace App\Http\Controllers\Csr\Reports;

use App\Http\Controllers\Controller;
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
                        SUM(csrw_csr_stocks.quantity) as quantity
                        FROM csrw_csr_stocks
                        JOIN hclass2 ON csrw_csr_stocks.cl2comb = hclass2.cl2comb
                        LEFT JOIN huom ON csrw_csr_stocks.uomcode = huom.uomcode
                        GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc;"
        );
        // dd($csr_report);

        $ward_report_from_csr =
            DB::table('csrw_wards_stocks')
            ->select('cl2comb', DB::raw('SUM(quantity) as quantity'))
            // ->where('from', 'CSR')
            ->groupBy('cl2comb')
            ->get();
        // dd($ward_report_from_csr);

        for ($csr = 0; $csr < count($csr_report); $csr++) {
            foreach ($ward_report_from_csr as $ward) {
                // if csr item has no match found in ward item
                // then push all detail
                // else only push csr info
                if ($csr_report[$csr]->cl2comb == $ward->cl2comb) {
                    $reports[$csr] = (object) [
                        'cl2comb' => $csr_report[$csr]->cl2comb,
                        'item_description' => $csr_report[$csr]->cl2desc,
                        'unit' => $csr_report[$csr]->uomdesc,
                        'unit_cost' => $csr_report[$csr]->selling_price,
                        'csr_quantity' => $csr_report[$csr]->quantity,
                        'ward_quantity' => $ward->quantity,
                        'total_beg_total_quantity' => $csr_report[$csr]->quantity + $ward->quantity,
                        'total_beg_total_cost' => ($csr_report[$csr]->quantity + $ward->quantity) * $csr_report[$csr]->selling_price,
                    ];
                } else {
                    $reports[$csr] = (object) [
                        'cl2comb' => $csr_report[$csr]->cl2comb,
                        'item_description' => $csr_report[$csr]->cl2desc,
                        'unit' => $csr_report[$csr]->uomdesc,
                        'unit_cost' => $csr_report[$csr]->selling_price,
                        'csr_quantity' => $csr_report[$csr]->quantity,
                        'ward_quantity' => 0,
                        'total_beg_total_quantity' => $csr_report[$csr]->quantity + 0,
                        'total_beg_total_cost' => ($csr_report[$csr]->quantity + 0) * $csr_report[$csr]->selling_price,
                    ];
                }
            }
        }


        // dd($reports);
        //////////////////////////////////////////////////

        return Inertia::render('Csr/Reports/Index', [
            'reports' => $reports
        ]);
    }
}
