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

        // get current supplies
        $medical_supplies = DB::table('csrw_wards_stocks')
            ->join('hclass2', 'csrw_wards_stocks.cl2comb', '=', 'hclass2.cl2comb')
            ->select('hclass2.cl2comb', 'hclass2.cl2desc', 'hclass2.uomcode', DB::raw('SUM(quantity) as quantity'))
            ->where('location', $authWardcode->wardcode)
            ->groupBy('hclass2.cl2comb', 'hclass2.cl2desc', 'hclass2.uomcode')
            ->get();

        $prices = WardsStocks::with('prices:cl2comb,selling_price,created_at', 'item_details:cl2comb,cl2desc,uomcode')->has('prices')
            ->where('location', $authWardcode->wardcode)
            ->groupBy('cl2comb')
            ->get('cl2comb');

        // get patients bills
        $bills = Patient::with([
            'admissionDateBill',
        ])
            // this will filter patients that hasn't been discharge
            ->whereHas('admissionDateBill', function ($q) use ($enccode) {
                $q->where('enccode', $enccode);
            })
            ->first();

        // dd($current_ward_supplies);

        return Inertia::render('Wards/Patients/Bill/Index', [
            'bills' => $bills,
            'medical_supplies' => $medical_supplies,
            'prices' => $prices,
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
