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
        $wardsInventory = DB::select(
            "SELECT ward.wardname as ward, item.cl2desc, SUM(ward_stock.quantity) as quantity
                FROM csrw_wards_stocks as ward_stock
                JOIN hward as ward ON ward.wardcode = ward_stock.location
                JOIN hclass2 as item ON item.cl2comb = ward_stock.cl2comb
                WHERE ward_stock.quantity > 0
                GROUP BY item.cl2desc, ward.wardname;"
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
