<?php

namespace App\Http\Controllers\Wards\Patients;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Patient;
use App\Models\WardsStocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PatientChargeController extends Controller
{
    public function index(Request $request)
    {
        $enccode = $request->enccode;

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $currentStocks = DB::table('hclass2')
            ->join('csrw_wards_stocks', 'csrw_wards_stocks.cl2comb', '=', 'hclass2.cl2comb')
            ->select(DB::raw("hclass2.cl2comb, hclass2.cl2desc, hclass2.uomcode, SUM(csrw_wards_stocks.quantity) as quantity, (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = csrw_wards_stocks.cl2comb ORDER BY created_at DESC) as 'price'"))
            ->where('csrw_wards_stocks.location', $authWardcode->wardcode)
            ->groupBy('hclass2.cl2comb', 'hclass2.cl2desc', 'hclass2.uomcode', 'csrw_wards_stocks.cl2comb')
            ->get();

        // get patients bills
        $bills = Patient::with([
            'admissionDateBill',
        ])
            // this will filter patients that hasn't been discharge
            ->whereHas('admissionDateBill', function ($q) use ($enccode) {
                $q->where('enccode', $enccode);
            })
            ->first();

        return Inertia::render('Wards/Patients/Bill/Index', [
            'bills' => $bills,
            'currentStocks' => $currentStocks,
        ]);
    }


    public function store(Request $request)
    {
        dd($request);
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
