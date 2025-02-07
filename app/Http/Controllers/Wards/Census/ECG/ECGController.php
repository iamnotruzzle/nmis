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
