<?php

namespace App\Http\Controllers\Wards\CsrInventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CsrInventoryController extends Controller
{
    public function index()
    {
        // $authWardcode = DB::select(
        //     "SELECT TOP 1
        //         l.wardcode
        //     FROM
        //         user_acc u
        //     INNER JOIN
        //         csrw_login_history l ON u.employeeid = l.employeeid
        //     WHERE
        //         l.employeeid = ?
        //     ORDER BY
        //         l.created_at DESC;
        //     ",
        //     [Auth::user()->employeeid]
        // );
        // $authCode = $authWardcode[0]->wardcode;

        // Retrieve cached values
        $authWardCode_cached = Cache::get('c_authWardCode_' . Auth::user()->employeeid);
        $wardCode = $authWardCode_cached;

        $csrInventory = DB::select(
            "SELECT item_conver.cl2comb_after, item.cl2desc as item_desc, SUM(quantity_after) as quantity
                FROM csrw_csr_item_conversion as item_conver
                JOIN hclass2 as item ON item.cl2comb = item_conver.cl2comb_after
                WHERE quantity_after > 0
                GROUP BY item_conver.cl2comb_after, item.cl2desc
                ORDER BY item.cl2desc ASC;"
        );

        $currentStock = DB::select(
            "SELECT item.cl2desc as item_desc, SUM(ward_stock.quantity) as quantity
                FROM csrw_wards_stocks as ward_stock
                JOIN hclass2 as item ON item.cl2comb = ward_stock.cl2comb
                LEFT JOIN csrw_request_stocks rs ON rs.id = ward_stock.request_stocks_id
                WHERE ward_stock.quantity > 0
                AND ward_stock.location = '$wardCode'
                AND (rs.id IS NULL OR rs.status = 'RECEIVED')
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
