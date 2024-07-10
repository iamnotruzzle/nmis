<?php

namespace App\Http\Controllers\Csr\Inventory\ItemConversion;

use App\Http\Controllers\Controller;
use App\Models\CsrItemConversion;
use App\Models\CsrItemConversionLogs;
use App\Models\CsrStocks;
use App\Models\CsrStocksLogs;
use App\Models\Item;
use App\Models\ItemPrices;
use Carbon\Carbon;
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

        $unit = Item::where('cl2comb', $request->cl2comb)->first('uomcode');

        $updated = CsrStocks::where('id', $request->csr_stock_id)
            ->update([
                'converted' => 'y',
            ]);

        $updatedLog = CsrStocksLogs::where('stock_id', $request->csr_stock_id)
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
            'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v'),
            'converted_by' => $converted_by,
            'remarks' => $request->remarks,
        ]);

        $convertedItemLog = CsrItemConversionLogs::create([
            'item_conversion_id' => $convertedItem->id,
            'csr_stock_id' => $convertedItem->csr_stock_id,
            'ris_no' => $convertedItem->ris_no,
            'chrgcode' => $convertedItem->chrgcode,
            'cl2comb_before' => $convertedItem->cl2comb_before,
            'quantity_before' => $convertedItem->quantity_before,
            'cl2comb_after' => $convertedItem->cl2comb_after,
            'quantity_after' => $convertedItem->quantity_after,
            'prev_qty' => $convertedItem->quantity_before,
            'new_qty' => $convertedItem->quantity_after,
            'supplierID' => $convertedItem->supplierID,
            'manufactured_date' => Carbon::parse($convertedItem->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($convertedItem->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  Carbon::parse($convertedItem->expiration_date)->format('Y-m-d H:i:s.v'),
            'converted_by' => $converted_by,
            'action' => 'CONVERT ITEM',
            'remarks' => $convertedItem->remarks,
        ]);

        $itemPrices = ItemPrices::create([
            'cl2comb' => $request->cl2comb_after,
            'price_per_unit' => $request->price_per_unit,
            'entry_by' => $converted_by,
            'ris_no' => $request->ris_no,
            'acquisition_price' => $request->acquisition_price,
            'hospital_price' => $request->hospital_price,
        ]);

        return redirect()->back();
    }

    public function update(CsrItemConversion $csrconvertdelivery, Request $request)
    {
        // dd($request);
        $updated_by = Auth::user()->employeeid;

        $updated_item =  $csrconvertdelivery->update([
            'quantity_after' => $request->quantity_after, // main category
            'update_by' => $updated_by
        ]);

        $log = CsrItemConversionLogs::where('item_conversion_id', $request->id)->first();
        // dd($log);
        $convertedItemLog = CsrItemConversionLogs::create([
            'item_conversion_id' => $log->item_conversion_id,
            'csr_stock_id' => $log->csr_stock_id,
            'ris_no' => $log->ris_no,
            'chrgcode' => $log->chrgcode,
            'cl2comb_before' => $log->cl2comb_after,
            'quantity_before' => $log->new_qty,
            'cl2comb_after' => $log->cl2comb_after,
            'prev_qty' => $log->new_qty,
            'new_qty' => $request->quantity_after,
            'supplierID' => $log->supplierID,
            'manufactured_date' => $log->manufactured_date,
            'delivered_date' => $log->delivered_date,
            'expiration_date' => $log->expiration_date,
            'action' => 'UPDATE QUANTITY',
            'remarks' => $request->remarks,
            'converted_by' => $log->converted_by,
            'updated_by' => $updated_by
        ]);

        // $convertedItem = CsrItemConversionLogs::where('item_conversion_id', $request->id)->first();

        return redirect()->back();
    }

    public function destroy(CsrItemConversion $csrconvertdelivery)
    {
        // dd($csrconvertdelivery);
        $updated_by = Auth::user()->employeeid;

        $log = CsrItemConversionLogs::where('item_conversion_id', $csrconvertdelivery->id)->first();
        // dd($log);

        if ($csrconvertdelivery->total_issued_qty == 0) {
            $csrconvertdelivery->delete();

            $convertedItemLog = CsrItemConversionLogs::create([
                'item_conversion_id' => $log->item_conversion_id,
                'csr_stock_id' => $log->csr_stock_id,
                'ris_no' => $log->ris_no,
                'chrgcode' => $log->chrgcode,
                'cl2comb_before' => $log->cl2comb_before,
                'quantity_before' => $log->quantity_before,
                'cl2comb_after' => $log->cl2comb_after,
                'prev_qty' => $log->prev_qty,
                'new_qty' => $log->new_qty,
                'supplierID' => $log->supplierID,
                'manufactured_date' => $log->manufactured_date,
                'delivered_date' => $log->delivered_date,
                'expiration_date' => $log->expiration_date,
                'action' => 'DELETED ITEM',
                'remarks' => '',
                'converted_by' => $log->converted_by,
                'updated_by' => $updated_by
            ]);

            // UPDATE STOCK
            CsrStocks::where('id', $log->csr_stock_id)
                ->update(['converted' => 'n']);

            // DELETE PRICE
            ItemPrices::where('ris_no', $log->ris_no)
                ->where('cl2comb', $log->cl2comb_after)
                ->delete();
        }

        return redirect()->back();
    }
}
