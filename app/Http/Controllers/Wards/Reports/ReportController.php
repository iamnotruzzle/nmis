<?php

namespace App\Http\Controllers\Wards\Reports;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{

    public function index(Request $request)
    {
        $reports = array();

        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();
        // dd($authWardcode->wardcode);

        if (is_null($request->from) || is_null($request->to)) {
            $from = Carbon::now()->startOfMonth();
            $to = Carbon::now();
            // dd($from);
            // $request->from = Carbon::now();
            // $request->to = Carbon::now();

            $ward_report = DB::select(
                "SELECT hclass2.cl2comb,
                hclass2.cl2desc as cl2desc,
                huom.uomdesc as uomdesc,
                (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'unit_cost',
                sum(CASE WHEN [from]='CSR' THEN quantity ELSE 0 END) as 'from_csr',
                SUM(ward.quantity) as 'total_stock',
                (SELECT SUM(CASE WHEN tscode = 'SURG' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'surgery',
                (SELECT SUM(CASE WHEN tscode = 'GYNE' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'obgyne',
                -- (SELECT SUM(CASE WHEN tscode = 'urology' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'urology',
                (SELECT SUM(CASE WHEN tscode = 'ORTHO' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ortho',
                (SELECT SUM(CASE WHEN tscode = 'PEDIA' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'pedia',
                -- (SELECT SUM(CASE WHEN tscode = 'med' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'med',
                (SELECT SUM(CASE WHEN tscode = 'OPHTH' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'optha',
                (SELECT SUM(CASE WHEN tscode = 'ENT' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ent',
                -- (SELECT SUM(CASE WHEN tscode = 'neuro' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'neuro',
                csrw_patient_charge_logs.charge_quantity as total_consumption
                FROM csrw_wards_stocks as ward
                JOIN hclass2 ON ward.cl2comb = hclass2.cl2comb
                LEFT JOIN huom ON ward.uomcode = huom.uomcode
                LEFT JOIN (
                    SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
                    FROM csrw_patient_charge_logs as charge
                    WHERE charge.[from] = 'CSR'
                    GROUP BY charge.itemcode
                ) csrw_patient_charge_logs ON ward.cl2comb = csrw_patient_charge_logs.itemcode
                WHERE ward.location LIKE '$authWardcode->wardcode' AND ward.created_at BETWEEN '$from' AND '$to'
                GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_patient_charge_logs.charge_quantity
                ORDER BY hclass2.cl2desc ASC;"
            );
        } else {
            $ward_report = DB::select(
                "SELECT hclass2.cl2comb,
                hclass2.cl2desc as cl2desc,
                huom.uomdesc as uomdesc,
                (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'unit_cost',
                sum(CASE WHEN [from]='CSR' THEN quantity ELSE 0 END) as 'from_csr',
                SUM(ward.quantity) as 'total_stock',
                (SELECT SUM(CASE WHEN tscode = 'SURG' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'surgery',
                (SELECT SUM(CASE WHEN tscode = 'GYNE' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'obgyne',
                -- (SELECT SUM(CASE WHEN tscode = 'urology' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'urology',
                (SELECT SUM(CASE WHEN tscode = 'ORTHO' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ortho',
                (SELECT SUM(CASE WHEN tscode = 'PEDIA' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'pedia',
                -- (SELECT SUM(CASE WHEN tscode = 'med' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'med',
                (SELECT SUM(CASE WHEN tscode = 'OPHTH' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'optha',
                (SELECT SUM(CASE WHEN tscode = 'ENT' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ent',
                -- (SELECT SUM(CASE WHEN tscode = 'neuro' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'neuro',
                csrw_patient_charge_logs.charge_quantity as total_consumption
                FROM csrw_wards_stocks as ward
                JOIN hclass2 ON ward.cl2comb = hclass2.cl2comb
                LEFT JOIN huom ON ward.uomcode = huom.uomcode
                LEFT JOIN (
                    SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
                    FROM csrw_patient_charge_logs as charge
                    WHERE charge.[from] = 'CSR'
                    GROUP BY charge.itemcode
                ) csrw_patient_charge_logs ON ward.cl2comb = csrw_patient_charge_logs.itemcode
                WHERE ward.location LIKE '$authWardcode->wardcode' AND ward.created_at BETWEEN '$request->from' AND '$request->to'
                GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_patient_charge_logs.charge_quantity
                ORDER BY hclass2.cl2desc ASC;"
            );
        }


        foreach ($ward_report as $e) {
            $reports[] = (object) [
                'cl2comb' => $e->cl2comb,
                'item_description' => $e->cl2desc,
                'unit' => $e->uomdesc,
                'unit_cost' => $e->unit_cost,
                'beginning_balance' => 'NA',
                'from_csr' => $e->from_csr,
                'total_stock' => $e->total_stock,
                'surgery' => $e->surgery,
                'obgyne' => $e->obgyne,
                // 'urology' => 'NA',
                'ortho' => $e->ortho,
                'pedia' => $e->pedia,
                // 'med' => 'NA',
                'optha' => $e->optha,
                'ent' => $e->ent,
                // 'neuro' => 'NA',
                'total_consumption' => $e->total_consumption,
                'total_cons_estimated_cost' => $e->total_consumption * $e->unit_cost,
                'ending_balance' => $e->total_stock - $e->total_consumption <= 0 ? 0 : $e->total_stock - $e->total_consumption,
                'actual_inventory' => $e->total_stock - $e->total_consumption <= 0 ? 0 : $e->total_stock - $e->total_consumption,
            ];
        }
        // dd($reports);

        return Inertia::render('Wards/Reports/Index', [
            'reports' => $reports
        ]);
    }
}
