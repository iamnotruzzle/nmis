<?php

namespace App\Http\Controllers\Wards\Census\ECG;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ECGController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : Carbon::today()->startOfDay();
        $to = $request->to ? Carbon::parse($request->to)->endOfDay() : Carbon::today()->endOfDay();

        // get auth wardcode
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

        $census = DB::select(
            "SELECT
                htypser.tscode,
                htypser.tsdesc,
                SUM(logs.quantity) AS total_quantity,
                SUM(logs.price_total) AS total_cost
            FROM csrw_patient_charge_logs AS logs
            JOIN htypser ON htypser.tscode = logs.tscode
            WHERE logs.entry_at = '$authCode'
            AND logs.itemcode = 'ECG'
            AND CAST(logs.created_at AS DATE) BETWEEN '$from' AND '$to'
            AND (
                logs.tscode = 'FAMED'
                OR logs.tscode = 'GYNE'
                OR logs.tscode = 'OB'
                OR logs.tscode = 'MED'
                OR logs.tscode = 'PEDIA'
                OR logs.tscode = 'SURG'
            )
            GROUP BY htypser.tscode, htypser.tsdesc;"
        );

        // $ecg_census_monthly = DB::select(
        //     "SELECT
        //         --htypser.tscode,
        //         DATENAME(MONTH, pcchrgdte) AS MONTH,
        //         MONTH(pcchrgdte) as M,
        //         htypser.tsdesc AS DEPARTMENT,
        //         SUM(charge.pchrgqty) AS QUANTITY,
        //         charge.pchrgup as 'UNIT COST',
        //         SUM(charge.pcchrgamt) AS 'TOTAL COST'
        //     FROM hpatchrg AS charge
        //     JOIN herlog AS erlog ON erlog.enccode = charge.enccode
        //     JOIN htypser ON htypser.tscode = erlog.tscode
        //     AND charge.pcchrgcod LIKE 'ER%'
        //     AND charge.itemcode = 'ECG'
        //     AND CAST(charge.pcchrgdte as DATE) BETWEEN '2023-01-01' AND '2023-12-31' -- change date filter
        //     GROUP BY htypser.tscode, htypser.tsdesc, charge.pchrgup, DATENAME(MONTH, pcchrgdte), MONTH(pcchrgdte)
        //     ORDER BY DEPARTMENT, M; -- sort department and month"
        // );

        // $er_fee_monthly = DB::select(
        //     "SELECT
        //         --htypser.tscode,
        //         DATENAME(MONTH, pcchrgdte) AS MONTH,
        //         MONTH(pcchrgdte) as M,
        //         htypser.tsdesc AS DEPARTMENT,
        //         SUM(charge.pchrgqty) AS QUANTITY,
        //         charge.pchrgup as 'UNIT COST',
        //         SUM(charge.pcchrgamt) AS 'TOTAL COST'
        //     FROM hpatchrg AS charge
        //     JOIN herlog AS erlog ON erlog.enccode = charge.enccode
        //     JOIN htypser ON htypser.tscode = erlog.tscode
        //     AND charge.pcchrgcod LIKE 'ER%'
        //     AND charge.itemcode = '04-A'
        //     AND CAST(charge.pcchrgdte as DATE) BETWEEN '2024-01-01' AND '2024-12-31' -- change date filter
        //     GROUP BY htypser.tscode, htypser.tsdesc, charge.pchrgup, DATENAME(MONTH, pcchrgdte), MONTH(pcchrgdte)
        //     ORDER BY DEPARTMENT, M; -- sort department and month"
        // );

        // // change the toecode filter if needed
        // $no_of_consultation_or_admission = DB::select(
        //     "SELECT count(herlog.enccode)
        //         FROM henctr
        //         INNER JOIN herlog ON  herlog.enccode = henctr.enccode
        //         AND (henctr.toecode = 'ER') -- ER CONSULTATION
        //         --AND (henctr.toecode = 'ERADM') -- ER ADMISSION
        //         AND(henctr.encdate BETWEEN '2024-01-01' AND '2024-12-31');"
        // );

        return Inertia::render('Wards/Census/ECG/Index', [
            'census' => $census,
        ]);
    }

    public function store(Request $request)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
