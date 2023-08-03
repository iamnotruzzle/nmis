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
        $report = [];

        $csr_report = DB::table('csrw_csr_stocks')
            ->join('hclass2', 'csrw_csr_stocks.cl2comb', '=', 'hclass2.cl2comb')
            ->leftJoin('huom', 'csrw_csr_stocks.uomcode', '=', 'huom.uomcode')
            ->select('hclass2.cl2comb', 'hclass2.cl2desc', 'huom.uomdesc', DB::raw('SUM(csrw_csr_stocks.quantity) as quantity'))
            // ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->groupBy('hclass2.cl2comb', 'hclass2.cl2desc', 'huom.uomdesc')
            ->get();

        $ward_report_from_csr =
            DB::table('csrw_wards_stocks')
            ->select('cl2comb', DB::raw('SUM(quantity) as quantity'))
            ->where('from', 'CSR')
            ->groupBy('cl2comb')
            ->get();

        // dd($ward_report_from_csr);
        //////////////////////////////////////////////////

        return Inertia::render('Csr/Reports/Index', []);
    }
}
