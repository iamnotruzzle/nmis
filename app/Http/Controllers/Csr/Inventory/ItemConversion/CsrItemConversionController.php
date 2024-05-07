<?php

namespace App\Http\Controllers\Csr\Inventory\ItemConversion;

use App\Http\Controllers\Controller;
use App\Models\CsrItemConversion;
use App\Models\CsrItemConvertionLogs;
use App\Models\CsrStocks;
use App\Models\CsrStocksLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CsrItemConversionController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        // dd($request);

        $converted_by = Auth::user()->employeeid;

        $updated = CsrStocks::where('id', $request->csr_stock_id)
            ->update([
                'converted' => 'y',
            ]);

        $updated = CsrStocksLogs::where('stock_id', $request->csr_stock_id)
            ->update([
                'converted' => 'y',
            ]);

        $convertedItem = CsrItemConversion::create([
            'csr_stock_id' => $request->csr_stock_id,
            'ris_no' => $request->ris_no,
            'chrgcode' => $request->chrgcode,
            'cl2comb_before' => $request->cl2comb_before,
            'quantity_before' => $request->quantity_before,
            'cl2comb_after' => $request->cl2comb_after,
            'quantity_after' => $request->quantity_after,
            'suppcode' => $request->suppcode,
            'manufactured_date' => $request->manufactured_date,
            'delivered_date' => $request->delivered_date,
            'expiration_date' => $request->expiration_date,
            'converted_by' => $converted_by,
        ]);

        $convertedItemLog = CsrItemConvertionLogs::create([
            'csr_stock_id' => $request->csr_stock_id,
            'ris_no' => $request->ris_no,
            'chrgcode' => $request->chrgcode,
            'cl2comb_before' => $request->cl2comb_before,
            'quantity_before' => $request->quantity_before,
            'cl2comb_after' => $request->cl2comb_after,
            'prev_qty' => 0,
            'new_qty' => $request->quantity_after,
            'suppcode' => $request->suppcode,
            'manufactured_date' => $request->manufactured_date,
            'delivered_date' => $request->delivered_date,
            'expiration_date' => $request->expiration_date,
            'action' => 'CONVERTED ITEM',
            'remarks' => '',
            'converted_by' => $converted_by,
        ]);

        return redirect()->back();
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
