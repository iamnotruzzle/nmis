<?php

namespace App\Http\Controllers\Wards\Tanks\TankStocks;

use App\Http\Controllers\Controller;
use App\Models\WardStocksTanks;
use App\Models\WardStocksTanksLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class WardTankStocksController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        // dd($request);

        $tank = WardStocksTanks::where('location', $request->location)
            ->where('id', $request->id)
            ->where('quantity', '!=', 0)
            ->orderBy('created_at', 'ASC')
            ->first();

        $new_qty =  $tank->quantity - $request->quantity;

        $tank->update([
            'quantity' => $new_qty,
        ]);

        $log = WardStocksTanksLogs::create([
            'request_stocks_id' => $tank->request_stocks_id,
            'request_stocks_detail_id' => $tank->request_stocks_detail_id,
            'stock_id' => $tank->id,
            'itemcode' => $request->itemcode,
            'location' => $request->location,
            'prev_qty' => $request->currentQty,
            'new_qty' => $new_qty,
            'action' => "UPDATE",
            'remarks' => $request->remarks,
            'entry_by' => $request->entry_by,
        ]);


        return Redirect::route('requesttankstocks.index');
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