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

        // only retrieve item with price
        $medical_supplies = Item::with('prices')->has('prices')
            ->get(['cl2comb', 'cl2desc', 'uomcode', 'cl2stat']);

        $current_stock = WardsStocks::with('prices:cl2comb,selling_price,created_at', 'item:cl2comb,cl2desc,uomcode')->has('prices')
            ->where('wardcode', $authWardcode->wardcode)
            ->get();

        // get current supplies
        $current_supplies = DB::table('csrw_wards_stocks')
            ->join('hclass2', 'csrw_wards_stocks.cl2comb', '=', 'hclass2.cl2comb')
            ->select('hclass2.cl2comb', 'hclass2.cl2desc', DB::raw('SUM(qty) as onhand'))
            ->where('wardcode', $authWardcode->wardcode)
            ->groupBy('hclass2.cl2comb', 'hclass2.cl2desc')
            ->get();

        // get patients bills
        $bills = Patient::with([
            'admissionDateBill',
        ])
            // this will filter patients that hasn't been discharge
            ->whereHas('admissionDate', function ($q) use ($enccode) {
                $q->where('enccode', $enccode);
            })
            ->get();

        return Inertia::render('Ward/Patients/Bill/Index', [
            'bills' => $bills,
            'medical_supplies' => $medical_supplies,
            'current_supplies' => $current_supplies,
            'current_stock' => $current_stock,
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
