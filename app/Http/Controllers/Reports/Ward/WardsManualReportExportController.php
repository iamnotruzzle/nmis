<?php

namespace App\Http\Controllers\Reports\Ward;

use App\Exports\WardStockManualReport;
use App\Http\Controllers\Controller;
use App\Models\WardsManualReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class WardsManualReportExportController extends Controller
{
    public function export(Request $request)
    {
        $export = array();

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $manual_reports = WardsManualReport::with('item_description:cl2comb,cl2desc', 'unit:uomcode,uomdesc', 'entryBy:employeeid,firstname,lastname', 'updatedBy:employeeid,firstname,lastname', 'ward:wardcode,wardname')
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
            ->where('wardcode', '=', $authWardcode->wardcode)
            ->get();

        // dd($manual_reports);

        foreach ($manual_reports as $e) {
            $export[] = (object) [
                'cl2comb' => $e->cl2comb,
                'item_description' => $e->item_description->cl2desc,
                'unit' => $e->unit == null ? null : $e->unit->uomdesc,
                'unit_cost' => $e->esti_budg_unit_cost,
                'beginning_balance' => $e->beginning_balance,
                'from_csr' => $e->received_from_csr,
                'total_stock' => $e->total_stock,
                'surgery' => $e->consumption_surgery,
                'obgyne' => $e->consumption_ob_gyne,
                // 'urology' => 'NA',
                'ortho' => $e->consumption_ortho,
                'pedia' => $e->consumption_pedia,
                // 'med' => 'NA',
                'optha' => $e->consumption_optha,
                'ent' => $e->consumption_ent,
                // 'neuro' => 'NA',
                'total_consumption' => $e->total_consumption_quantity,
                'total_cons_estimated_cost' => $e->total_consumption_cost,
                'ending_balance' => $e->ending_balance,
                'actual_inventory' => $e->actual_inventory,
            ];
        }

        return Excel::download(new WardStockManualReport($export), 'report.xlsx');
    }
}
