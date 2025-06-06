<?php

namespace App\Http\Controllers\Reports\Ward;

use App\Exports\WardStocksReport;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class WardStocksReportController extends Controller
{
    public function export(Request $request)
    {
        $reports = array();

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        $authWardcode = DB::select(
            "SELECT TOP 1
                l.wardcode
            FROM
                user_acc u
            INNER JOIN
                csrw_login_history l ON u.employeeid = l.employeeid
            WHERE
                l.employeeid = ?
            ORDER BY
                l.created_at DESC;
            ",
            [Auth::user()->employeeid]
        );
        $authCode = $authWardcode[0]->wardcode;
        // dd($authWardcode->wardcode);

        if (is_null($request->from) || is_null($request->to)) {
            $ward_report = DB::select(
                "SELECT hclass2.cl2comb,
                hclass2.cl2desc as cl2desc,
                huom.uomdesc as uomdesc,
                csrw_location_stock_balance.ending_balance as ending_balance,
                csrw_location_stock_balance.beginning_balance as beginning_balance,
                (SELECT TOP 1 price_per_unit FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'unit_cost',
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
                LEFT JOIN (
                    SELECT stockbal.cl2comb, SUM(stockbal.ending_balance) as ending_balance, SUM(stockbal.beginning_balance) as beginning_balance
                    FROM csrw_location_stock_balance as stockbal
                    WHERE stockbal.location LIKE '$authCode'
                    GROUP BY stockbal.cl2comb
                ) csrw_location_stock_balance ON ward.cl2comb = csrw_location_stock_balance.cl2comb
                WHERE ward.location LIKE '$authCode' AND ward.created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
                AND ward.is_consumable IS NULL
                GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_patient_charge_logs.charge_quantity, csrw_location_stock_balance.ending_balance, csrw_location_stock_balance.beginning_balance
                ORDER BY hclass2.cl2desc ASC;"
            );
        } else {
            $ward_report = DB::select(
                "SELECT hclass2.cl2comb,
                hclass2.cl2desc as cl2desc,
                huom.uomdesc as uomdesc,
                csrw_location_stock_balance.ending_balance as ending_balance,
                csrw_location_stock_balance.beginning_balance as beginning_balance,
                (SELECT TOP 1 price_per_unit FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'unit_cost',
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
                LEFT JOIN (
                    SELECT stockbal.cl2comb, SUM(stockbal.ending_balance) as ending_balance, SUM(stockbal.beginning_balance) as beginning_balance
                    FROM csrw_location_stock_balance as stockbal
                    WHERE stockbal.location LIKE '$authCode'
                    GROUP BY stockbal.cl2comb
                ) csrw_location_stock_balance ON ward.cl2comb = csrw_location_stock_balance.cl2comb
                WHERE ward.location LIKE '$authCode AND ward.created_at BETWEEN '$from' AND '$to'
                AND ward.is_consumable IS NULL
                GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_patient_charge_logs.charge_quantity, csrw_location_stock_balance.ending_balance, csrw_location_stock_balance.beginning_balance
                ORDER BY hclass2.cl2desc ASC;"
            );
        }

        foreach ($ward_report as $e) {
            $total_cons_estimated_cost = $e->total_consumption * $e->unit_cost;
            $reports[] = (object) [
                'cl2comb' => $e->cl2comb,
                'item_description' => $e->cl2desc,
                'unit' => $e->uomdesc,
                'unit_cost' => $e->unit_cost,
                'beginning_balance' => $e->beginning_balance,
                'from_csr' => $e->from_csr + $e->total_consumption,
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
                'total_cons_estimated_cost' => (string)$total_cons_estimated_cost,
                'ending_balance' => $e->ending_balance,
                'actual_inventory' => ($e->from_csr + $e->total_consumption) - $e->total_consumption,
            ];
        }

        return Excel::download(new WardStocksReport($reports), 'report.xlsx');
    }
}
