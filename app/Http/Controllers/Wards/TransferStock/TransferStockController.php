<?php

namespace App\Http\Controllers\Wards\TransferStock;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\UserDetail;
use App\Models\WardsStocks;
use App\Models\WardTransferStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class TransferStockController extends Controller
{

    public function index(Request $request)
    {
        $employeeids = UserDetail::where('empstat', 'A')->get('employeeid');

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $currentWardStocks = WardsStocks::with(['item_details:cl2comb,cl2desc', 'brand_details:id,name'])
            ->where('location', $authWardcode->wardcode)
            ->where('quantity', '!=', 0)
            ->get();

        $transferredStock = WardTransferStock::with('ward_stock')
            ->when($request->search, function ($query, $value) {
                $query->where('employeeid', 'LIKE', '%' . $value . '%');
            })
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
            ->orderBy('created_at', 'DESC')
            ->paginate(15);

        $wards = Location::where('wardstat', 'A')->get(['wardcode', 'wardname']);

        return Inertia::render('Wards/TransferStock/Index', [
            'currentWardStocks' => $currentWardStocks,
            'transferredStock' => $transferredStock,
            'wards' => $wards,
            'employeeids' => $employeeids
        ]);
    }


    public function store(Request $request)
    {

        $request->validate([
            'role' => 'required',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('image', 'public');
        } else {
            $image = null;
        }

        $user = User::create([
            'employeeid' => $request->employeeid,
            'password' => bcrypt($request->password),
            'image' => $image,
        ]);

        return Redirect::route('transferstock.index');
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
