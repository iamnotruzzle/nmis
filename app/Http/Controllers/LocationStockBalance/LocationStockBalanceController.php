<?php

namespace App\Http\Controllers\LocationStockBalance;

use App\Http\Controllers\Controller;
use App\Models\WardsStocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LocationStockBalanceController extends Controller
{
    public function index()
    {
        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $currentWardStocks = WardsStocks::with('item_details:cl2comb,cl2desc')
            ->where('location', $authWardcode->wardcode)
            ->where('from', 'CSR')
            ->get();

        return Inertia::render('Wards/Stocks/Index', [
            // 'items' => $items,
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
