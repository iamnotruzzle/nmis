<?php

namespace App\Http\Controllers\LocationStockBalance;

use App\Http\Controllers\Controller;
use App\Models\LocationStockBalance;
use App\Models\WardsStocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
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

        $locationStockBalance = LocationStockBalance::with(['item:cl2comb,cl2desc', 'user_detail'])
            ->where('location', $authWardcode->wardcode)
            ->paginate(10);

        return Inertia::render('Balance/Index', [
            'currentWardStocks' => $currentWardStocks,
            'locationStockBalance' => $locationStockBalance,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cl2comb' => 'required',
            'ending_balance' => 'required',
            'starting_balance' => 'required',
        ]);

        LocationStockBalance::create([
            'location' => $request->location,
            'cl2comb' => $request->cl2comb,
            'ending_balance' => $request->ending_balance,
            'starting_balance' => $request->starting_balance,
            'entry_by' => $request->entry_by,
        ]);

        return redirect()->back();
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
