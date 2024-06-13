<?php

namespace App\Http\Controllers\Wards\Consignment;

use App\Http\Controllers\Controller;
use App\Models\WardsStocks;
use App\Models\WardsStocksLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class WardConsignmentController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $entry_by = Auth::user()->employeeid;

        $request->validate([
            'fund_source' => 'required',
            'cl2comb' => 'required',
            'quantity' => 'required',
            // 'delivered_date' => 'required',
            'expiration_date' => 'required',
        ]);

        $consignment = WardsStocks::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'stock_id' => null,
            'location' => $request->authLocation,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'chrgcode' => $request->fund_source,
            'quantity' => $request->quantity,
            'from' => 'CONSIGNMENT',
            'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v'),
        ]);
        // dd($consignment);

        $wardStockLogs = WardsStocksLogs::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'stock_id' => null,
            'location' => $request->authLocation,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'chrgcode' => $request->fund_source,
            'prev_qty' => 0,
            'new_qty' => $request->quantity,
            'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v'),
            'action' => 'CREATE',
            'remarks' => null,
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
