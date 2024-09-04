<?php

namespace App\Http\Controllers\Csr\WardsInventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use Inertia\Inertia;

class WardsInventoryController extends Controller
{
    public function index()
    {
        //   check session
        $hasSession = Sessions::where('id', Session::getId())->exists();

        if ($hasSession) {
            $user = Auth::user();

            $authWardcode = DB::table('csrw_users')
                ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
                ->select('csrw_login_history.wardcode')
                ->where('csrw_login_history.employeeid', $user->employeeid)
                ->orderBy('csrw_login_history.created_at', 'desc')
                ->first();


            Sessions::where('id', Session::getId())->update([
                // 'user_id' => $request->login,
                'location' => $authWardcode->wardcode,
            ]);
        }
        // end check session

        $wardsInventory = DB::select(
            "SELECT ward_stock.id, ward.wardname as ward, ward_stock.ris_no, item.cl2desc, ward_stock.quantity, ward_stock.expiration_date
                FROM csrw_wards_stocks as ward_stock
                JOIN hward as ward ON ward.wardcode = ward_stock.location
                JOIN hclass2 as item ON item.cl2comb = ward_stock.cl2comb
                WHERE ward_stock.quantity > 0;"
        );

        return Inertia::render('Csr/WardsInventory/Index', [
            'wardsInventory' => $wardsInventory,
        ]);
    }

    // public function store(Request $request)
    // {
    //     //
    // }

    // public function update(Request $request, $id)
    // {
    //     //
    // }

    // public function destroy($id)
    // {
    //     //
    // }
}
