<?php

namespace App\Http\Controllers\Wards\Reports;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class ReportController extends Controller
{

    public function index(Request $request)
    {
        //   check session
        $hasSession = Sessions::where('id', Session::getId())->exists();

        if ($hasSession) {
            $user = Auth::user();

            $authWardcode = DB::table('csrw_users')
                ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
                ->select('csrw_login_history.wardcode')
                ->where('csrw_login_history.employeeid', $user->employeeid)
                ->orderBy('csrw_login_history.created_at', 'desc')
                ->first();


            Sessions::where('id', Session::getId())->update([
                // 'user_id' => $request->login,
                'location' => $authWardcode->wardcode,
            ]);
        }
        // end check session

        $reports = array();

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();
        // dd($authWardcode->wardcode);

        // OLD query
        // ITEMS ARE COMBINED EVEN IF THEY HAVE DIFF. PRICES
        // if (is_null($request->from) || is_null($request->to)) {
        //     $ward_report = DB::select(
        //         "SELECT hclass2.cl2comb,
        //         hclass2.cl2desc as cl2desc,
        //         huom.uomdesc as uomdesc,
        //         csrw_location_stock_balance.ending_balance as ending_balance,
        //         csrw_location_stock_balance.beginning_balance as beginning_balance,
        //         (SELECT TOP 1 price_per_unit FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'unit_cost',
        //         sum(CASE WHEN [from]='CSR' THEN quantity ELSE 0 END) as 'from_csr',
        //         (SELECT SUM(CASE WHEN tscode = 'SURG' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'surgery',
        //         (SELECT SUM(CASE WHEN tscode = 'GYNE' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'obgyne',
        //         -- (SELECT SUM(CASE WHEN tscode = 'urology' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'urology',
        //         (SELECT SUM(CASE WHEN tscode = 'ORTHO' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ortho',
        //         (SELECT SUM(CASE WHEN tscode = 'PEDIA' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'pedia',
        //         -- (SELECT SUM(CASE WHEN tscode = 'med' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'med',
        //         (SELECT SUM(CASE WHEN tscode = 'OPHTH' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'optha',
        //         (SELECT SUM(CASE WHEN tscode = 'ENT' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ent',
        //         -- (SELECT SUM(CASE WHEN tscode = 'neuro' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'neuro',
        //         csrw_patient_charge_logs.charge_quantity as total_consumption
        //         FROM csrw_wards_stocks as ward
        //         JOIN hclass2 ON ward.cl2comb = hclass2.cl2comb
        //         LEFT JOIN huom ON ward.uomcode = huom.uomcode
        //         LEFT JOIN (
        //             SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
        //             FROM csrw_patient_charge_logs as charge
        //             WHERE charge.[from] = 'CSR'
        //             GROUP BY charge.itemcode
        //         ) csrw_patient_charge_logs ON ward.cl2comb = csrw_patient_charge_logs.itemcode
        //         LEFT JOIN (
        //             SELECT stockbal.cl2comb, SUM(stockbal.ending_balance) as ending_balance, SUM(stockbal.beginning_balance) as beginning_balance
        //             FROM csrw_location_stock_balance as stockbal
        //             WHERE stockbal.location LIKE '$authWardcode->wardcode'
        //             GROUP BY stockbal.cl2comb
        //         ) csrw_location_stock_balance ON ward.cl2comb = csrw_location_stock_balance.cl2comb
        //         WHERE ward.location LIKE '$authWardcode->wardcode' AND ward.created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
        //         AND ward.is_consumable IS NULL
        //         GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_patient_charge_logs.charge_quantity, csrw_location_stock_balance.ending_balance, csrw_location_stock_balance.beginning_balance
        //         ORDER BY hclass2.cl2desc ASC;"
        //     );
        // } else {
        //     $ward_report = DB::select(
        //         "SELECT hclass2.cl2comb,
        //         hclass2.cl2desc as cl2desc,
        //         huom.uomdesc as uomdesc,
        //         csrw_location_stock_balance.ending_balance as ending_balance,
        //         csrw_location_stock_balance.beginning_balance as beginning_balance,
        //         (SELECT TOP 1 price_per_unit FROM csrw_item_prices WHERE cl2comb = hclass2.cl2comb ORDER BY created_at DESC) as 'unit_cost',
        //         sum(CASE WHEN [from]='CSR' THEN quantity ELSE 0 END) as 'from_csr',
        //         (SELECT SUM(CASE WHEN tscode = 'SURG' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'surgery',
        //         (SELECT SUM(CASE WHEN tscode = 'GYNE' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'obgyne',
        //         -- (SELECT SUM(CASE WHEN tscode = 'urology' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'urology',
        //         (SELECT SUM(CASE WHEN tscode = 'ORTHO' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ortho',
        //         (SELECT SUM(CASE WHEN tscode = 'PEDIA' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'pedia',
        //         -- (SELECT SUM(CASE WHEN tscode = 'med' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'med',
        //         (SELECT SUM(CASE WHEN tscode = 'OPHTH' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'optha',
        //         (SELECT SUM(CASE WHEN tscode = 'ENT' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ent',
        //         -- (SELECT SUM(CASE WHEN tscode = 'neuro' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'neuro',
        //         csrw_patient_charge_logs.charge_quantity as total_consumption
        //         FROM csrw_wards_stocks as ward
        //         JOIN hclass2 ON ward.cl2comb = hclass2.cl2comb
        //         LEFT JOIN huom ON ward.uomcode = huom.uomcode
        //         LEFT JOIN (
        //             SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
        //             FROM csrw_patient_charge_logs as charge
        //             WHERE charge.[from] = 'CSR'
        //             GROUP BY charge.itemcode
        //         ) csrw_patient_charge_logs ON ward.cl2comb = csrw_patient_charge_logs.itemcode
        //         LEFT JOIN (
        //             SELECT stockbal.cl2comb, SUM(stockbal.ending_balance) as ending_balance, SUM(stockbal.beginning_balance) as beginning_balance
        //             FROM csrw_location_stock_balance as stockbal
        //             WHERE stockbal.location LIKE '$authWardcode->wardcode'
        //             GROUP BY stockbal.cl2comb
        //         ) csrw_location_stock_balance ON ward.cl2comb = csrw_location_stock_balance.cl2comb
        //         WHERE ward.location LIKE '$authWardcode->wardcode' AND ward.created_at BETWEEN '$from' AND '$to'
        //         AND ward.is_consumable IS NULL
        //         GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_patient_charge_logs.charge_quantity, csrw_location_stock_balance.ending_balance, csrw_location_stock_balance.beginning_balance
        //         ORDER BY hclass2.cl2desc ASC;"
        //     );
        // }

        // // NEW
        if (is_null($request->from) || is_null($request->to)) {
            // $ward_report = DB::select(
            //     "SELECT hclass2.cl2comb,
            //     hclass2.cl2desc as cl2desc,
            //     huom.uomdesc as uomdesc,
            //     csrw_location_stock_balance.ending_balance as ending_balance,
            //     csrw_location_stock_balance.beginning_balance as beginning_balance,
            //     csrw_item_prices.price_per_unit as 'unit_cost',
            //     sum(CASE WHEN [from]='CSR' THEN quantity ELSE 0 END) as 'from_csr',
            //     sum(CASE WHEN [from]='WARD' THEN quantity ELSE 0 END) as 'from_ward',
            //     (SELECT SUM(CASE WHEN tscode = 'SURG' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'surgery',
            //     (SELECT SUM(CASE WHEN tscode = 'GYNE' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'obgyne',
            //     -- (SELECT SUM(CASE WHEN tscode = 'urology' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'urology',
            //     (SELECT SUM(CASE WHEN tscode = 'ORTHO' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ortho',
            //     (SELECT SUM(CASE WHEN tscode = 'PEDIA' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'pedia',
            //     -- (SELECT SUM(CASE WHEN tscode = 'med' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'med',
            //     (SELECT SUM(CASE WHEN tscode = 'OPHTH' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'optha',
            //     (SELECT SUM(CASE WHEN tscode = 'ENT' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ent',
            //     -- (SELECT SUM(CASE WHEN tscode = 'neuro' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'neuro',
            //     csrw_patient_charge_logs.charge_quantity as total_consumption,
            //     SUM(csrw_ward_transfer_stock.transferred_qty) as transferred_qty
            //     FROM csrw_wards_stocks as ward
            //     JOIN hclass2 ON ward.cl2comb = hclass2.cl2comb
            //     JOIN csrw_item_prices ON csrw_item_prices.ris_no = ward.ris_no
            //     LEFT JOIN huom ON ward.uomcode = huom.uomcode
            //     LEFT JOIN (
            //         SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
            //         FROM csrw_patient_charge_logs as charge
            //         WHERE charge.[from] = 'CSR'
            //         GROUP BY charge.itemcode
            //     ) csrw_patient_charge_logs ON ward.cl2comb = csrw_patient_charge_logs.itemcode
            //     LEFT JOIN (
            //         SELECT stockbal.cl2comb, SUM(stockbal.ending_balance) as ending_balance, SUM(stockbal.beginning_balance) as beginning_balance
            //         FROM csrw_location_stock_balance as stockbal
            //         WHERE stockbal.location LIKE '$authWardcode->wardcode'
            //         GROUP BY stockbal.cl2comb
            //     ) csrw_location_stock_balance ON ward.cl2comb = csrw_location_stock_balance.cl2comb
            //     LEFT JOIN (
            //         SELECT ward_stock_id, SUM(quantity) as transferred_qty
            //         FROM csrw_ward_transfer_stock
            //         WHERE status = 'RECEIVED'
            //         GROUP BY ward_stock_id
            //     ) csrw_ward_transfer_stock ON ward.id = csrw_ward_transfer_stock.ward_stock_id -- Join with ward_stock_id
            //     WHERE ward.location LIKE '$authWardcode->wardcode' AND ward.created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
            //     AND ward.is_consumable IS NULL
            //     GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_patient_charge_logs.charge_quantity, csrw_location_stock_balance.ending_balance, csrw_location_stock_balance.beginning_balance, csrw_item_prices.price_per_unit
            //     ORDER BY hclass2.cl2desc ASC;"
            // );
            $ward_report = DB::select(
                "SELECT hclass2.cl2comb,
                        hclass2.cl2desc as cl2desc,
                        huom.uomdesc as uomdesc,
                        csrw_location_stock_balance.ending_balance as ending_balance,
                        csrw_location_stock_balance.beginning_balance as beginning_balance,
                        csrw_item_prices.price_per_unit as 'unit_cost',
                        sum(CASE WHEN [from]='CSR' THEN quantity ELSE 0 END) as 'from_csr',
                        sum(CASE WHEN [from]='WARD' THEN quantity ELSE 0 END) as 'from_ward',
                        (SELECT SUM(CASE WHEN tscode = 'SURG' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'surgery',
                        (SELECT SUM(CASE WHEN tscode = 'GYNE' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'obgyne',
                        (SELECT SUM(CASE WHEN tscode = 'ORTHO' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ortho',
                        (SELECT SUM(CASE WHEN tscode = 'PEDIA' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'pedia',
                        (SELECT SUM(CASE WHEN tscode = 'OPHTH' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'optha',
                        (SELECT SUM(CASE WHEN tscode = 'ENT' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ent',
                        csrw_patient_charge_logs.charge_quantity as total_consumption,
                        SUM(csrw_ward_transfer_stock.transferred_qty) as transferred_qty
                FROM csrw_wards_stocks as ward
                JOIN hclass2 ON ward.cl2comb = hclass2.cl2comb
                JOIN csrw_item_prices ON csrw_item_prices.ris_no = ward.ris_no
                LEFT JOIN huom ON ward.uomcode = huom.uomcode
                LEFT JOIN (
                    SELECT charge.itemcode, SUM(charge.quantity) as charge_quantity, SUM(charge.price_total) as charge_total
                    FROM csrw_patient_charge_logs as charge
                    WHERE charge.[from] = 'CSR'
                    GROUP BY charge.itemcode
                ) csrw_patient_charge_logs ON ward.cl2comb = csrw_patient_charge_logs.itemcode
                LEFT JOIN (
                    SELECT stockbal.ward_stock_id,
                        stockbal.ending_balance as ending_balance,
                        stockbal.beginning_balance as beginning_balance
                    FROM csrw_location_stock_balance as stockbal
                    WHERE stockbal.location LIKE '$authWardcode->wardcode'
                    AND DATEPART(month, stockbal.created_at) = DATEPART(month, DATEADD(month, -1, GETDATE()))
                    AND DATEPART(year, stockbal.created_at) = DATEPART(year, DATEADD(month, -1, GETDATE()))
                    AND DAY(stockbal.created_at) <= 25
                ) csrw_location_stock_balance ON ward.id = csrw_location_stock_balance.ward_stock_id -- Use ward.id as the identifier
                LEFT JOIN (
                    SELECT ward_stock_id, SUM(quantity) as transferred_qty
                    FROM csrw_ward_transfer_stock
                    WHERE status = 'RECEIVED'
                    GROUP BY ward_stock_id
                ) csrw_ward_transfer_stock ON ward.id = csrw_ward_transfer_stock.ward_stock_id
                WHERE ward.location LIKE '$authWardcode->wardcode'
                AND ward.created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
                AND ward.is_consumable IS NULL
                GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_patient_charge_logs.charge_quantity, csrw_location_stock_balance.ending_balance, csrw_location_stock_balance.beginning_balance, csrw_item_prices.price_per_unit
                ORDER BY hclass2.cl2desc ASC;"
            );
        } else {
            $ward_report = DB::select(
                "SELECT hclass2.cl2comb,
                hclass2.cl2desc as cl2desc,
                huom.uomdesc as uomdesc,
                csrw_location_stock_balance.ending_balance as ending_balance,
                csrw_location_stock_balance.beginning_balance as beginning_balance,
                csrw_item_prices.price_per_unit as 'unit_cost',
                sum(CASE WHEN [from]='CSR' THEN quantity ELSE 0 END) as 'from_csr',
                sum(CASE WHEN [from]='WARD' THEN quantity ELSE 0 END) as 'from_ward',
                (SELECT SUM(CASE WHEN tscode = 'SURG' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'surgery',
                (SELECT SUM(CASE WHEN tscode = 'GYNE' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'obgyne',
                -- (SELECT SUM(CASE WHEN tscode = 'urology' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'urology',
                (SELECT SUM(CASE WHEN tscode = 'ORTHO' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ortho',
                (SELECT SUM(CASE WHEN tscode = 'PEDIA' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'pedia',
                -- (SELECT SUM(CASE WHEN tscode = 'med' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'med',
                (SELECT SUM(CASE WHEN tscode = 'OPHTH' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'optha',
                (SELECT SUM(CASE WHEN tscode = 'ENT' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'ent',
                -- (SELECT SUM(CASE WHEN tscode = 'neuro' THEN quantity ELSE 0 END) FROM csrw_patient_charge_logs as cl WHERE cl.itemcode = hclass2.cl2comb) as 'neuro',
                csrw_patient_charge_logs.charge_quantity as total_consumption,
                SUM(csrw_ward_transfer_stock.transferred_qty) as transferred_qty
                FROM csrw_wards_stocks as ward
                JOIN hclass2 ON ward.cl2comb = hclass2.cl2comb
                JOIN csrw_item_prices ON csrw_item_prices.ris_no = ward.ris_no
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
                    WHERE stockbal.location LIKE '$authWardcode->wardcode'
                    GROUP BY stockbal.cl2comb
                ) csrw_location_stock_balance ON ward.cl2comb = csrw_location_stock_balance.cl2comb
                LEFT JOIN (
                    SELECT ward_stock_id, SUM(quantity) as transferred_qty
                    FROM csrw_ward_transfer_stock
                    WHERE status = 'RECEIVED'
                    GROUP BY ward_stock_id
                ) csrw_ward_transfer_stock ON ward.id = csrw_ward_transfer_stock.ward_stock_id
                WHERE ward.location LIKE '$authWardcode->wardcode' AND ward.created_at BETWEEN '$from' AND '$to'
                AND ward.is_consumable IS NULL
                GROUP BY hclass2.cl2comb, hclass2.cl2desc, huom.uomdesc, csrw_patient_charge_logs.charge_quantity, csrw_location_stock_balance.ending_balance, csrw_location_stock_balance.beginning_balance, csrw_item_prices.price_per_unit
                ORDER BY hclass2.cl2desc ASC;"
            );
        }
        // dd($ward_report);

        foreach ($ward_report as $e) {
            $total_cons_estimated_cost = $e->total_consumption * $e->unit_cost;
            $reports[] = (object) [
                'cl2comb' => $e->cl2comb,
                'item_description' => $e->cl2desc,
                'unit' => $e->uomdesc,
                'unit_cost' => $e->unit_cost,
                'beginning_balance' => $e->beginning_balance,
                'from_csr' => $e->from_csr + $e->total_consumption,
                'from_ward' => $e->from_ward,
                'total_beg_bal' => $e->beginning_balance + $e->from_csr + $e->from_ward,
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
                'transferred_qty' => $e->transferred_qty,
                'ending_balance' => $e->ending_balance,
                'actual_inventory' => 0
            ];
        }
        // dd($reports);

        return Inertia::render('Wards/Reports/Index', [
            'reports' => $reports
        ]);

        // // maintenance page
        // return Inertia::render('UnderMaintenancePage', [
        //     'reports' => $reports
        // ]);
    }
}
