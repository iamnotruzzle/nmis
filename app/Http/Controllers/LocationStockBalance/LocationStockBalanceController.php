<?php

namespace App\Http\Controllers\LocationStockBalance;

use App\Http\Controllers\Controller;
use App\Models\LocationStockBalance;
use App\Models\WardsStocks;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class LocationStockBalanceController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;

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

        $locationStockBalance = LocationStockBalance::with(['item:cl2comb,cl2desc', 'entry_by', 'updated_by'])
            ->where('location', $authWardcode->wardcode)
            ->when(
                $request->from,
                function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }
            )
            ->when(
                $request->to,
                function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }
            )
            ->whereHas('item', function ($q) use ($searchString) {
                $q->where('cl2desc', 'LIKE', '%' . $searchString . '%');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return Inertia::render('Balance/Index', [
            'currentWardStocks' => $currentWardStocks,
            'locationStockBalance' => $locationStockBalance,
        ]);
    }

    public function store(Request $request)
    {
        $isExist = false;
        $date = Carbon::now()->subDays(30); // get last 30 days

        $s = LocationStockBalance::where('cl2comb', $request->cl2comb)
            ->where('created_at', '>=', $date)
            ->count();
        // dd($s);

        // if the item is listed in the last 30 days.
        // then error a message will pop out saying the item is listed
        // in the last 30days
        if ($s != 0) {
            $isExist = true;
        }

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

    public function update(LocationStockBalance $stockbal, Request $request)
    {
        $request->validate([
            'cl2comb' => 'required',
            'ending_balance' => 'required',
            'starting_balance' => 'required',
        ]);

        $stockbal->update([
            'location' => $request->location,
            'cl2comb' => $request->cl2comb,
            'ending_balance' => $request->ending_balance,
            'starting_balance' => $request->starting_balance,
            'updated_by' => $request->entry_by,
        ]);

        // dd($lsb);

        return redirect()->back();
    }

    public function destroy(LocationStockBalance $stockbal)
    {
        $stockbal->delete();

        return redirect()->back();
    }
}
