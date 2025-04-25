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
        //         DATENAME(MONTH, charge.pcchrgdte) AS MONTH, -- Get full month name
        //         htypser.tsdesc AS DEPARTMENT,
        //         SUM(charge.pchrgqty) AS QUANTITY,
        //         charge.pchrgup AS [UNIT COST],
        //         SUM(charge.pcchrgamt) AS [TOTAL COST]
        //     FROM hpatchrg AS charge
        //     JOIN herlog AS erlog ON erlog.enccode = charge.enccode
        //     JOIN htypser ON htypser.tscode = erlog.tscode
        //     WHERE charge.pcchrgcod LIKE 'ER%'
        //     AND charge.itemcode = 'ECG'
        //     AND CAST(charge.pcchrgdte AS DATE) BETWEEN '2023-01-01' AND '2023-12-31'
        //     GROUP BY DATENAME(MONTH, charge.pcchrgdte), MONTH(charge.pcchrgdte), htypser.tsdesc, charge.pchrgup
        //     ORDER BY DEPARTMENT ASC, MONTH(charge.pcchrgdte) ASC;"
        // );

        // can also be Total no. Of ER patients
        // $er_fee_monthly = DB::select(
        //     "SELECT
        //     htypser.tsdesc AS DEPARTMENT,
        //     SUM(charge.pchrgqty) AS QUANTITY,
        //     charge.pchrgup as 'UNIT COST',
        //     SUM(charge.pcchrgamt) AS 'TOTAL COST'
        // FROM hpatchrg AS charge
        // JOIN herlog AS erlog ON erlog.enccode = charge.enccode
        // JOIN htypser ON htypser.tscode = erlog.tscode
        // WHERE charge.pcchrgcod LIKE 'ER%'
        // AND charge.itemcode = 'EROPH' -- EROPH = Emergency room fee
        // AND CAST(charge.pcchrgdte AS DATE) BETWEEN '2024-01-01' AND '2024-12-31'
        // GROUP BY htypser.tsdesc, charge.pchrgup;"
        // );

        // can also be Total no of admitted at ER
        // // change the toecode filter if needed
        // $no_of_consultation_or_admission = DB::select(
        // "SELECT
        //     htypser.tsdesc AS DEPARTMENT,
        //     COUNT(*) AS TOTAL_COUNT
        // FROM henctr
        // INNER JOIN herlog AS erlog ON erlog.enccode = henctr.enccode
        // JOIN htypser ON htypser.tscode = erlog.tscode
        // WHERE henctr.toecode = 'ER' -- ER CONSULTATION
        // --WHERE henctr.toecode = 'ERADM' -- ER ADMISSION
        // AND henctr.encdate BETWEEN '2023-01-01' AND '2023-12-31'
        // GROUP BY htypser.tsdesc
        // ORDER BY TOTAL_COUNT DESC;"
        // );

        // number of discharge at ER
        // $no_of_discharge_at_er = DB::select(
        //     "SELECT
        //         htypser.tsdesc AS DEPARTMENT,
        //         SUM(charge.pchrgqty) AS QUANTITY,
        //         charge.pchrgup as 'UNIT COST',
        //         SUM(charge.pcchrgamt) AS 'TOTAL COST'
        //     FROM hpatchrg AS charge
        //     JOIN herlog AS erlog ON erlog.enccode = charge.enccode
        //     JOIN htypser ON htypser.tscode = erlog.tscode
        //     WHERE charge.pcchrgcod LIKE 'ER%'
        //     AND charge.itemcode = 'EROPH' -- EROPH = Emergency room fee
        //     AND erlog.dispcode = 'TRASH'
        //     AND CAST(charge.pcchrgdte AS DATE) BETWEEN '2025-03-01' AND '2025-03-31'
        //     GROUP BY htypser.tsdesc, charge.pchrgup;"
        // );

        // query to get patient that overstayed in ER
        // more than 4 hours meaning it overstays
        // $overstayed_patients = DB::select(
        //     "SELECT
        //             htypser.tsdesc AS DEPARTMENT,
        //             COUNT(*) AS TOTAL_OVERSTAYED,
        //             CAST(AVG(DATEDIFF(SECOND, erlog.erdate, erlog.erdtedis)) / 3600.0 AS DECIMAL(10,2)) AS AVG_OVERSTAY_HOURS
        //         FROM herlog AS erlog
        //         JOIN htypser ON htypser.tscode = erlog.tscode
        //         WHERE
        //             erlog.erdtedis IS NOT NULL
        //             AND erlog.erdate IS NOT NULL
        //             AND erlog.erdate < erlog.erdtedis  -- Ensure valid stay period
        //             AND DATEDIFF(MINUTE, erlog.erdate, erlog.erdtedis) > 240  -- More than 4 hours
        //             AND erlog.erdate BETWEEN '2025-01-01' AND '2025-01-31'
        //         GROUP BY htypser.tsdesc
        //         ORDER BY htypser.tsdesc;"
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
