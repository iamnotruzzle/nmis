<?php

namespace App\Http\Controllers\Wards\ConvertItem;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\WardsStocks;
use App\Models\WardsStocksLogs;
use App\Rules\CsrStockBalanceNotDeclaredYetRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ConvertItemController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        // dd($request);


        $entry_by = Auth::user()->employeeid;

        $wardStock = WardsStocks::where('id', $request->ward_stock_id)->first();

        // dd($e[0]);
        $request->validate(
            [
                "cl2comb" => ['required', new CsrStockBalanceNotDeclaredYetRule($request->cl2comb)],
            ],
        );

        $wardStock->update([
            'quantity' => $wardStock->quantity - (int)$request->qty_to_convert,
            'converted_quantity' => $wardStock->converted_quantity + (int)$request->qty_to_convert,
        ]);

        $stockUomcode = Item::where('cl2comb', $request->to)->first('uomcode');

        // dd($stockUomcode->uomcode);

        $addConvertedStock = WardsStocks::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'stock_id' => null,
            'location' => $wardStock->location,
            'cl2comb' => $request->to,
            'uomcode' => $stockUomcode->uomcode,
            'chrgcode' => $wardStock->chrgcode,
            'quantity' => $request->equivalent_quantity,
            'converted_from_ward_stock_id' => $wardStock->id,
            'from' => 'CSR',
            'is_converted' => 'y',
            'manufactured_date' => Carbon::parse($wardStock->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($wardStock->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  Carbon::parse($wardStock->expiration_date)->format('Y-m-d H:i:s.v'),
        ]);


        $addConvertedStockLogs = WardsStocksLogs::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'stock_id' => null,
            'location' => $wardStock->location,
            'cl2comb' => $request->to,
            'uomcode' => $stockUomcode->uomcode,
            'chrgcode' => $wardStock->chrgcode,
            'prev_qty' => 0,
            'new_qty' => $request->equivalent_quantity,
            'converted_from_ward_stock_id' => $wardStock->id,
            'from' => $wardStock->from,
            'is_converted' => 'y',
            'action' => 'CONVERT ITEM',
            'manufactured_date' => Carbon::parse($wardStock->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($wardStock->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  Carbon::parse($wardStock->expiration_date)->format('Y-m-d H:i:s.v'),
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
