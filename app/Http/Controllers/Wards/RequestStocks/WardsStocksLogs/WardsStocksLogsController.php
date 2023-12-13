<?php

namespace App\Http\Controllers\Wards\RequestStocks\WardsStocksLogs;

use App\Http\Controllers\Controller;
use App\Models\WardsStocksMedSupp;
use App\Models\WardsStocksMedSuppLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class WardsStocksLogsController extends Controller
{
    public function index()
    {
        //
    }


    public function store(Request $request)
    {
        $request->validate([
            'remarks' => 'required'
        ]);

        $entry_by = Auth::user()->employeeid;

        $prevStockDetails = WardsStocksMedSupp::where('id', $request->ward_stock_id)->first();

        $updatedWardStock = WardsStocksMedSupp::where('id', $request->ward_stock_id)
            ->update([
                'quantity' => $request->quantity
            ]);

        $wardStockLogs = WardsStocksMedSuppLogs::create([
            'request_stocks_id' => $prevStockDetails->request_stocks_id,
            'request_stocks_detail_id' => $prevStockDetails->request_stocks_detail_id,
            'stock_id' => $prevStockDetails->stock_id,
            'location' => $prevStockDetails->location,
            'cl2comb' => $prevStockDetails->cl2comb,
            'uomcode' => $prevStockDetails->uomcode,
            'brand' => $prevStockDetails->brand,
            'chrgcode' => $prevStockDetails->chrgcode,
            'prev_qty' => $request->current_quantity,
            'new_qty' => $request->quantity,
            'converted' => $prevStockDetails->converted,
            'manufactured_date' => $prevStockDetails->manufactured_date,
            'delivered_date' => $prevStockDetails->delivered_date,
            'expiration_date' => $prevStockDetails->expiration_date,
            'action' => 'UPDATE',
            'remarks' => $request->remarks,
            'entry_by' => $entry_by,
        ]);

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
