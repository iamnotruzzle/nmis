<?php

namespace App\Http\Controllers\Wards\Tanks\TankStocks;

use App\Http\Controllers\Controller;
use App\Models\WardStocksTanks;
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
            ->where('itemcode', $request->itemcode)
            ->where('quantity', '!=', 0)
            ->orderBy('created_at', 'ASC')
            ->first();

        $tank->update([
            'quantity' => $tank->quantity - $request->quantity,
        ]);

        // dd($tank);

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
