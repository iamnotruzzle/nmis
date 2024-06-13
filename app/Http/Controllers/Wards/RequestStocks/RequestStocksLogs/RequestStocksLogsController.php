<?php

namespace App\Http\Controllers\Wards\RequestStocks\RequestStocksLogs;

use App\Http\Controllers\Controller;
use App\Models\WardsStocks;
use App\Models\WardsStocksLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class RequestStocksLogsController extends Controller
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

        $prevStockDetails = WardsStocks::where('id', $request->ward_stock_id)->first();

        // dd($prevStockDetails);

        $updatedWardStock = WardsStocks::where('id', $request->ward_stock_id)
            ->update([
                'quantity' => $prevStockDetails->quantity - $request->quantity
            ]);

        $wardStockLogs = WardsStocksLogs::create([
            'request_stocks_id' => $prevStockDetails->request_stocks_id,
            'request_stocks_detail_id' => $prevStockDetails->request_stocks_detail_id,
            'stock_id' => $prevStockDetails->stock_id,
            'location' => $prevStockDetails->location,
            'cl2comb' => $prevStockDetails->cl2comb,
            'uomcode' => $prevStockDetails->uomcode,
            'chrgcode' => $prevStockDetails->chrgcode,
            'prev_qty' => $request->current_quantity,
            'new_qty' => $request->quantity,
            'converted_from_ward_stock_id' => $prevStockDetails->id,
            'manufactured_date' => Carbon::parse($prevStockDetails->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivery_date' => Carbon::parse($prevStockDetails->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' => Carbon::parse($prevStockDetails->expiration_date)->format('Y-m-d H:i:s.v'),
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
