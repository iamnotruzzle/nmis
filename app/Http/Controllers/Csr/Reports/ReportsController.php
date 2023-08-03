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

        $csr_report = DB::table('csrw_csr_stocks')
            ->join('hclass2', 'csrw_csr_stocks.cl2comb', '=', 'hclass2.cl2comb')
            ->leftJoin('huom', 'csrw_csr_stocks.uomcode', '=', 'huom.uomcode')
            ->leftJoin('csrw_item_prices', 'csrw_csr_stocks.cl2comb', '=', 'csrw_item_prices.cl2comb')
            ->select('hclass2.cl2comb', 'hclass2.cl2desc', 'huom.uomdesc', 'csrw_item_prices.selling_price', DB::raw('SUM(csrw_csr_stocks.quantity) as quantity'))
            // ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->groupBy('hclass2.cl2comb', 'hclass2.cl2desc', 'huom.uomdesc', 'csrw_item_prices.selling_price')
            ->get();

        $ward_report_from_csr =
            DB::table('csrw_wards_stocks')
            ->select('cl2comb', DB::raw('SUM(quantity) as quantity'))
            ->where('from', 'CSR')
            ->groupBy('cl2comb')
            ->get();

        // foreach ($csr_report as $item) {
        //     $reports[] = (object) [
        //         'cl2comb' => $item->cl2comb,
        //         'item' => $item->cl2desc,
        //         'unit' => $item->uomdesc,
        //         'unit_cost' => $item->selling_price,
        //         'csr_quantity' => $item->quantity,
        //     ];
        // }

        for ($csr = 0; $csr < count($csr_report); $csr++) {
            foreach ($ward_report_from_csr as $ward) {
                if ($csr_report[$csr]->cl2comb == $ward->cl2comb) {
                    $reports[$csr] = (object) [
                        'cl2comb' => $csr_report[$csr]->cl2comb,
                        'item_description' => $csr_report[$csr]->cl2desc,
                        'unit' => $csr_report[$csr]->uomdesc,
                        'unit_cost' => $csr_report[$csr]->selling_price,
                        'csr_quantity' => $csr_report[$csr]->quantity,
                        'ward_quantity' => $ward->quantity,
                        'total_beg_total_quantity' => 0,
                        'total_beg_total_cost' => 0,
                    ];
                } else {
                    $reports[$csr] = (object) [
                        'cl2comb' => $csr_report[$csr]->cl2comb,
                        'item_description' => $csr_report[$csr]->cl2desc,
                        'unit' => $csr_report[$csr]->uomdesc,
                        'unit_cost' => $csr_report[$csr]->selling_price,
                        'csr_quantity' => $csr_report[$csr]->quantity,
                        'ward_quantity' => 0,
                        'total_beg_total_quantity' => 0,
                        'total_beg_total_cost' => 0,
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
