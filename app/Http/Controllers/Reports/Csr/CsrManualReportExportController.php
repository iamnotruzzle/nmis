<?php

namespace App\Http\Controllers\Reports\Csr;

use App\Exports\CsrManualReport;
use App\Http\Controllers\Controller;
use App\Models\CsrManualReport as ModelsCsrManualReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CsrManualReportExportController extends Controller
{
    public function export(Request $request)
    {
        $export = array();

        $res = ModelsCsrManualReport::with('item_description:cl2comb,cl2desc', 'unit:uomcode,uomdesc')
            ->when(
                $request->from,
                function ($query, $value) {
                    $query->whereDate('created_at', '>=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->to,
                function ($query, $value) {
                    $query->whereDate('created_at', '<=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->get();

        // dd($res);

        foreach ($res as $e) {
            // dd($e->item_description->cl2desc);

            $export[] = (object) [
                'item_description' => $e->item_description->cl2desc,
                'unit' => $e->unit == null ? null : $e->unit->uomdesc,
                'unit_cost' => $e->unit_cost,
                'csr_quantity' => $e->csr_beg_bal_quantity, // csr starting balance
                'csr_total_cost' => $e->csr_beg_bal_total_cost,
                'ward_quantity' => $e->wards_beg_bal_quantity, // ward starting balance
                'ward_total_cost' => $e->wards_beg_bal_total_cost,
                'total_beg_total_quantity' => $e->total_beg_bal_total_quantity,
                'total_beg_total_cost' => $e->total_beg_bal_total_cost,
                'supplies_issued_to_wards_quantity' => $e->supp_issued_to_wards_total_quantity,
                'supplies_issued_to_wards_total_cost' => $e->supp_issued_to_wards_total_cost,
                'consumption_quantity' => $e->consumption_quantity,
                'consumption_total_cost' => $e->consumption_total_cost,
                'csr_quantity_ending_bal' => $e->csr_end_bal_quantity, // csr ending balance
                'csr_total_cost_ending_bal' => $e->csr_end_bal_total_cost,
                'ward_quantity_ending_bal' => $e->wards_end_bal_quantity,
                'ward_total_cost_ending_bal' => $e->wards_end_bal_total_cost, // ward ending balance
                'total_end_total_quantity' => $e->total_end_bal_total_quantity,
                'total_end_total_cost' => $e->total_end_bal_total_cost,
            ];
        }

        // dd($export);

        return Excel::download(new CsrManualReport($export), 'report.xlsx');
    }
}
