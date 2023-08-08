<?php

namespace App\Http\Controllers\Wards\Consignment;

use App\Http\Controllers\Controller;
use App\Models\WardsStocksLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class WardConsignmentController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        dd($request);
        $request->validate([
            'brand' => 'required',
            'cl2comb' => 'required',
            'quantity' => 'required',
            'expiration_date' => 'required',
        ]);

        // $wardStockLogs = WardsStocksLogs::create([
        //     'request_stocks_id' => $prevStockDetails->request_stocks_id,
        //     'request_stocks_detail_id' => $prevStockDetails->request_stocks_detail_id,
        //     'stock_id' => $prevStockDetails->stock_id,
        //     'location' => $prevStockDetails->location,
        //     'cl2comb' => $prevStockDetails->cl2comb,
        //     'brand' => $prevStockDetails->brand,
        //     'chrgcode' => $prevStockDetails->chrgcode,
        //     'prev_qty' => $request->current_quantity,
        //     'new_qty' => $request->quantity,
        //     'manufactured_date' => $prevStockDetails->manufactured_date,
        //     'delivered_date' => $prevStockDetails->delivered_date,
        //     'expiration_date' => $prevStockDetails->expiration_date,
        //     'action' => 'UPDATE',
        //     'remarks' => $request->remarks,
        //     'entry_by' => $entry_by,
        // ]);

        return Redirect::route('requeststocks.index');
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
