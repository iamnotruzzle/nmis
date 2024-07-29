<?php

namespace App\Http\Controllers\Csr\WardsInventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WardsInventoryController extends Controller
{
    public function index()
    {
        $wardsStocks = DB::select(
            "SELECT ward.wardname as ward, ward_stock.ris_no, item.cl2desc, ward_stock.quantity, ward_stock.expiration_date
                FROM csrw_wards_stocks as ward_stock
                JOIN hward as ward ON ward.wardcode = ward_stock.location
                JOIN hclass2 as item ON item.cl2comb = ward_stock.cl2comb
                WHERE ward_stock.quantity > 0;"
        );

        // return Inertia::render('Csr/Inventory/Categories/Index', [
        //     'csrSuppliesSubCategory' => $csrSuppliesSubCategory,
        // ]);
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
