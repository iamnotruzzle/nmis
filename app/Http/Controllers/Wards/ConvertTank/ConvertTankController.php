<?php

namespace App\Http\Controllers\Wards\ConvertTank;

use App\Http\Controllers\Controller;
use App\Models\WardStocksTanks;
use App\Models\WardStocksTanksLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ConvertTankController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        // dd($request);


        $entry_by = Auth::user()->employeeid;

        $wardStock = WardStocksTanks::where('id', $request->ward_stock_id)->first();

        // dd($e[0]);
        // $request->validate(
        //     [
        //         "cl2comb" => ['required', new CsrStockBalanceNotDeclaredYetRule($request->cl2comb)],
        //     ],
        // );

        $wardStock->update([
            'quantity' => $wardStock->quantity - (int)$request->qty_to_convert,
            'converted_quantity' => $wardStock->converted_quantity + (int)$request->qty_to_convert,
        ]);

        $addConvertedStock = WardStocksTanks::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'stock_id' => null,
            'location' => $wardStock->location,
            'itemcode' => $request->to,
            'quantity' => $request->equivalent_quantity,
            'converted_from_ward_stock_id' => $wardStock->id,
            'from' => 'CSR',
            'is_converted' => 'y',
        ]);


        $addConvertedStockLogs = WardStocksTanksLogs::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'stock_id' => null,
            'location' => $wardStock->location,
            'itemcode' => $request->to,
            'prev_qty' => 0,
            'new_qty' => $request->equivalent_quantity,
            'converted_from_ward_stock_id' => $wardStock->id,
            'from' => $wardStock->from,
            'is_converted' => 'y',
            'action' => 'CONVERT ITEM',
            'entry_by' => $entry_by,
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
