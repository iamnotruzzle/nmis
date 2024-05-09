<?php

namespace App\Http\Controllers\Csr\Inventory\ItemConversion;

use App\Http\Controllers\Controller;
use App\Models\CsrItemConversion;
use App\Models\CsrItemConversionLogs;
use App\Models\CsrStocks;
use App\Models\CsrStocksLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'supplierID' => $request->supplierID,
            'manufactured_date' => $request->manufactured_date,
            'delivered_date' => $request->delivered_date,
            'expiration_date' => $request->expiration_date,
            'converted_by' => $converted_by,
        ]);

        $convertedItemLog = CsrItemConversionLogs::create([
            'item_conversion_id' => $convertedItem->id,
            'csr_stock_id' => $request->csr_stock_id,
            'ris_no' => $request->ris_no,
            'chrgcode' => $request->chrgcode,
            'cl2comb_before' => $request->cl2comb_before,
            'quantity_before' => $request->quantity_before,
            'cl2comb_after' => $request->cl2comb_after,
            'prev_qty' => 0,
            'new_qty' => $request->quantity_after,
            'supplierID' => $request->supplierID,
            'manufactured_date' => $request->manufactured_date,
            'delivered_date' => $request->delivered_date,
            'expiration_date' => $request->expiration_date,
            'action' => 'CONVERTED ITEM',
            'remarks' => '',
            'converted_by' => $converted_by,
        ]);

        return redirect()->back();
    }

    public function update(CsrItemConversion $convertitem, Request $request)
    {
        $updated_by = Auth::user()->employeeid;

        // dd($request);

        $convertedItem = CsrItemConversionLogs::where('item_conversion_id', $request->id)->first();

        // dd($convertedItem);

        CsrItemConversion::where('id', $request->id)
            ->update([
                'cl2comb_after' => $request->cl2comb_after,
                'quantity_after' => $request->quantity_after,
                'updated_by' => $updated_by,
            ]);

        // dd($item);

        $convertedItemLog = CsrItemConversionLogs::create([
            'item_conversion_id' => $convertedItem->item_conversion_id,
            'csr_stock_id' => $convertedItem->csr_stock_id,
            'ris_no' => $convertedItem->ris_no,
            'chrgcode' => $convertedItem->chrgcode,
            'cl2comb_before' => $convertedItem->cl2comb_after,
            'quantity_before' => $convertedItem->new_qty,
            'cl2comb_after' => $request->cl2comb_after,
            'prev_qty' => $convertedItem->new_qty,
            'new_qty' => $request->quantity_after,
            'supplierID' => $convertedItem->supplierID,
            'manufactured_date' => $convertedItem->manufactured_date,
            'delivered_date' => $convertedItem->delivered_date,
            'expiration_date' => $convertedItem->expiration_date,
            'action' => 'UPDATE ITEM',
            'remarks' => '', // todo
            'converted_by' => $convertedItem->converted_by,
            'updated_by' => $updated_by,
        ]);

        return redirect()->back();
    }

    public function destroy($id)
    {
        //
    }
}
