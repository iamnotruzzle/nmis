<?php

namespace App\Http\Controllers\Wards\Reports;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{

    public function index(Request $request)
    {
        $reports = array();

        // dd($request->to);

        if (is_null($request->from) || is_null($request->to)) {
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now();
            // dd($from);
            // $request->from = Carbon::now();
            // $request->to = Carbon::now();

            $csr_report = DB::select(
                "SELECT hclass2.cl2comb,
                hclass2.cl2desc,
                huom.uomdesc as UNIT,
                (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'UNIT COST',
                sum(CASE WHEN [from]='CSR' THEN quantity ELSE 0 END) as 'RECEIVED FROM CSR',
                SUM(ward.quantity) as 'TOTAL STOCK'
                FROM csrw_wards_stocks as ward
                JOIN hclass2 ON ward.cl2comb = hclass2.cl2comb
                LEFT JOIN huom ON ward.uomcode = huom.uomcode
                GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc
                ORDER BY hclass2.cl2desc ASC;"
            );
        } else {
            // $csr_report = DB::select(
            //     "SELECT hclass2.cl2comb,
            //     hclass2.cl2desc,
            //     huom.uomdesc as UNIT,
            //     (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'UNIT COST',
            //     SUM(csrw_wards_stocks.quantity) as 'TOTAL STOCK'
            //     FROM csrw_wards_stocks
            //     JOIN hclass2 ON csrw_wards_stocks.cl2comb = hclass2.cl2comb
            //     LEFT JOIN huom ON csrw_wards_stocks.uomcode = huom.uomcode
            //     GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_wards_stocks.quantity
            //     ORDER BY hclass2.cl2desc ASC;"
            // );
        }
        // dd($csr_report);

        // foreach ($csr_report as $e) {
        //     $reports[] = (object) [
        //         'item_description' => $e->cl2desc,
        //         'unit' => $e->uomdesc,
        //         'unit_cost' => $e->selling_price,
        //         'csr_quantity' => $e->csr_quantity,
        //         'csr_total_cost' => $e->csr_quantity * $e->selling_price,
        //         'ward_quantity' => $e->wards_quantity,
        //         'ward_total_cost' => $e->wards_quantity * $e->selling_price,
        //         'total_beg_total_quantity' => $e->csr_quantity + $e->wards_quantity,
        //         'total_beg_total_cost' => ($e->csr_quantity + $e->wards_quantity) * $e->selling_price,
        //         'supplies_issued_to_wards_quantity' => $e->wards_quantity + $e->consumption_quantity,
        //         'supplies_issued_to_wards_total_cost' => ($e->wards_quantity + $e->consumption_quantity) * $e->selling_price,
        //         'consumption_quantity' => $e->consumption_quantity,
        //         'consumption_total_cost' => $e->consumption_total_cost,
        //         'csr_quantity_ending_bal' => $e->csr_quantity,
        //         'csr_total_cost_ending_bal' => $e->csr_quantity * $e->selling_price,
        //         'ward_quantity_ending_bal' => ($e->wards_quantity + $e->consumption_quantity) - $e->consumption_quantity,
        //         'ward_total_cost_ending_bal' => (($e->wards_quantity + $e->consumption_quantity) - $e->consumption_quantity) * $e->selling_price,
        //         'total_end_total_quantity' => $e->csr_quantity + ($e->wards_quantity + $e->consumption_quantity) - $e->consumption_quantity,
        //         'total_end_total_cost' => ($e->csr_quantity + ($e->wards_quantity + $e->consumption_quantity) - $e->consumption_quantity) * $e->selling_price,
        //     ];
        // }
        // dd($reports);

        return Inertia::render('Csr/Reports/Index', [
            'reports' => $reports
        ]);
    }
}
