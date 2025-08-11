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
    private function getAuthWardcode()
    {
        return DB::select(
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
    }


    public function index(Request $request)
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : Carbon::today()->startOfDay();
        $to = $request->to ? Carbon::parse($request->to)->endOfDay() : Carbon::today()->endOfDay();

        // get auth wardcode
        $authWardcode = $this->getAuthWardcode();
        $authCode = $authWardcode[0]->wardcode;

        $census = DB::table('csrw_patient_charge_logs as logs')
            ->join('htypser', 'htypser.tscode', '=', 'logs.tscode')
            ->select([
                'htypser.tscode',
                'htypser.tsdesc',
                DB::raw('SUM(logs.quantity) AS total_quantity'),
                DB::raw('SUM(logs.price_total) AS total_cost')
            ])
            ->where('logs.entry_at', $authCode)
            ->where('logs.itemcode', 'ECG')
            ->whereDate('logs.created_at', '>=', $from)
            ->whereDate('logs.created_at', '<=', $to)
            ->whereIn('logs.tscode', ['FAMED', 'GYNE', 'OB', 'MED', 'PEDIA', 'SURG'])
            ->groupBy('htypser.tscode', 'htypser.tsdesc')
            ->get();

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

        // get six hour range filter
        // $six_hour_range_filter = DB::select(
        //     "SELECT type.tsdesc AS Department,
        //         RIGHT('0' + CAST(DATEPART(HOUR, erlog.erdate) / 6 * 6 AS VARCHAR(2)), 2) + ':00 - ' +
        //         RIGHT('0' + CAST((DATEPART(HOUR, erlog.erdate) / 6 * 6 + 5) AS VARCHAR(2)), 2) + ':59' AS Six_Hour_Range,
        //         COUNT(DISTINCT erlog.enccode) AS Total_No_Patients
        //     FROM herlog AS erlog
        //     JOIN htypser AS type ON type.tscode = erlog.tscode
        //     WHERE erlog.erdate >= '2025-03-01' AND erlog.erdate < '2025-03-31'
        //     GROUP BY
        //         type.tsdesc,
        //         DATEPART(HOUR, erlog.erdate) / 6
        //     ORDER BY
        //         Six_Hour_Range, Department;"
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
