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
        $request->validate([
            'remarks' => 'required'
        ]);

        $entry_by = Auth::user()->employeeid;

        $wardStock = WardsStocks::where('id', $request->ward_stock_id)->first();
        $csrStockiD = $wardStock->stock_id;
        $prevQuantity = $wardStock->quantity;
        // dd($prevQuantity);
        // dd((int)$prevQuantity - (int)$request->quantity);

        $updateWardStock = WardsStocks::where('id', $request->ward_stock_id)
            ->update([
                'quantity' => (int)$prevQuantity - (int)$request->quantity
            ]);
        // dd($updatedWardStock);

        $csrStock = CsrItemConversion::where('id', $csrStockiD)->first();
        $updateCsrStock = CsrItemConversion::where('id', $csrStockiD)
            ->update([
                'quantity_after' => (int)$csrStock->quantity_after + (int)$request->quantity,
                'total_issued_qty' => (int)$csrStock->total_issued_qty - (int)$request->quantity
            ]);

        $requestStockDetail = RequestStocksDetails::where('id', $wardStock->request_stocks_detail_id)->first();
        $updateRequestStockDetail = RequestStocksDetails::where('id', $wardStock->request_stocks_detail_id)
            ->update([
                'approved_qty' => (int)$requestStockDetail->approved_qty - (int)$request->quantity
            ]);

        $wardStockLogs = WardsStocksLogs::create([
            'request_stocks_id' => $wardStock->request_stocks_id,
            'request_stocks_detail_id' => $wardStock->request_stocks_detail_id,
            'ris_no' => $wardStock->ris_no,
            'stock_id' => $wardStock->stock_id,
            'location' => $wardStock->location,
            'cl2comb' => $wardStock->cl2comb,
            'uomcode' => $wardStock->uomcode,
            'chrgcode' => $wardStock->chrgcode,
            'prev_qty' => $request->current_quantity,
            'new_qty' => $request->quantity,
            'converted_from_ward_stock_id' => $wardStock->id,
            'manufactured_date' => Carbon::parse($wardStock->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivery_date' => Carbon::parse($wardStock->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' => Carbon::parse($wardStock->expiration_date)->format('Y-m-d H:i:s.v'),
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
