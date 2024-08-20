<?php

namespace App\Http\Controllers\Wards\RequestStocks\RequestStocksLogs;

use App\Http\Controllers\Controller;
use App\Models\CsrItemConversion;
use App\Models\RequestStocksDetails;
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
        // dd($request);
        // $request->validate([
        //     'remarks' => 'required'
        // ]);

        $entry_by = Auth::user()->employeeid;
        $ward_stock_id = $request->ward_stock_id;

        $updateWardStock = WardsStocks::where('id', $ward_stock_id)
            ->update([
                'is_consumable' => 'y',
                'average' => $request->average,
                'total_usage' => (int)$request->quantity * (int)$request->average,
            ]);

        $wardStock = WardsStocks::where('id', $request->ward_stock_id)->first();

        $wardStockLogs = WardsStocksLogs::create([
            'request_stocks_id' => $wardStock->request_stocks_id,
            'request_stocks_detail_id' => $wardStock->request_stocks_detail_id,
            'ris_no' => $wardStock->ris_no,
            'stock_id' => $wardStock->id,
            'is_consumable' => 'y',
            'location' => $wardStock->location,
            'cl2comb' => $wardStock->cl2comb,
            'uomcode' => $wardStock->uomcode,
            'chrgcode' => $wardStock->fund_source,
            'prev_qty' => 0,
            'new_qty' => $wardStock->quantity,
            'average' => $wardStock->average,
            'total_usage' => $wardStock->total_usage,
            'manufactured_date' => Carbon::parse($wardStock->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($wardStock->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  $wardStock->expiration_date,
            'action' => 'CONVERT IT TO CONSUMABLE',
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
