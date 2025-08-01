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
