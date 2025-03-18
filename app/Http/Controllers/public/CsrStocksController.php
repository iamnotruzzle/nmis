<?php

namespace App\Http\Controllers\public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CsrStocksController extends Controller
{

    public function index()
    {
        $csrInventory = DB::select(
            "SELECT item.cl2comb as ID, item.cl2desc as ITEM, SUM(quantity_after) as TOTAL_QUANTITY
                FROM csrw_csr_item_conversion as stock
                JOIN hclass2 as item ON stock.cl2comb_after = item.cl2comb
                GROUP BY item.cl2comb, item.cl2desc
                ORDER BY item.cl2desc ASC;"
        );

        $wardsInventory = DB::select(
            "SELECT ward.wardname as ward, item.cl2desc as item, SUM(ward_stock.quantity) as quantity
                FROM csrw_wards_stocks as ward_stock
                JOIN hward as ward ON ward.wardcode = ward_stock.location
                JOIN hclass2 as item ON item.cl2comb = ward_stock.cl2comb
                GROUP BY item.cl2desc, ward.wardname;"
        );

        return Inertia::render('Csr/Public/CsrStocks/Index', [
            'csrInventory' => $csrInventory,
            'wardsInventory' => $wardsInventory,
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
