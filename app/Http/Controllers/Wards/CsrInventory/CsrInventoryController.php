<?php

namespace App\Http\Controllers\Wards\CsrInventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CsrInventoryController extends Controller
{
    public function index()
    {
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $csrInventory = DB::select(
            "SELECT item.cl2desc as item_desc, quantity_after as quantity
                FROM csrw_csr_item_conversion as item_conver
                JOIN hclass2 as item ON item.cl2comb = item_conver.cl2comb_after
                WHERE quantity_after > 0
                ORDER BY item.cl2desc ASC;"
        );

        $currentStock = DB::select(
            "SELECT item.cl2desc as item_desc, SUM(ward_stock.quantity) as quantity
                FROM csrw_wards_stocks as ward_stock
                JOIN hclass2 as item ON item.cl2comb = ward_stock.cl2comb
                WHERE ward_stock.quantity > 0
                AND location = '$authWardcode->wardcode'
                GROUP BY item.cl2desc
                ORDER BY item.cl2desc ASC;"
        );

        // dd($currentStock);

        return Inertia::render('Wards/CsrInventory/Index', [
            'csrInventory' => $csrInventory,
            'currentStock' => $currentStock,
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
