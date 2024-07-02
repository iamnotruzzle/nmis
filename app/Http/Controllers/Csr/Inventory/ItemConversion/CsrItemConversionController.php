<?php

namespace App\Http\Controllers\Csr\Inventory\ItemConversion;

use App\Http\Controllers\Controller;
use App\Models\CsrItemConversion;
use App\Models\CsrItemConversionLogs;
use App\Models\CsrStocks;
use App\Models\CsrStocksLogs;
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

        $updated = CsrStocks::where('id', $request->csr_stock_id)
            ->update([
                'converted' => 'y',
            ]);

        $updatedLogs = CsrStocksLogs::where('stock_id', $request->csr_stock_id)
            ->update([
                'converted' => 'y',
            ]);

        if ($request->cl2comb_after == null) {
            $convertedItem = CsrItemConversion::create([
                'csr_stock_id' => $request->csr_stock_id,
                'ris_no' => $request->ris_no,
                'chrgcode' => $request->chrgcode,
                'cl2comb_before' => $request->cl2comb_before,
                'quantity_before' => $request->quantity_before,
                'cl2comb_after' => $request->cl2comb_before,
                'quantity_after' => $request->quantity_after,
                'supplierID' => $request->supplierID,
                'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
                'delivered_date' =>  Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
                'expiration_date' =>  Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v'),
                'converted_by' => $converted_by,
            ]);

            $convertedItemLog = CsrItemConversionLogs::create([
                'item_conversion_id' => $convertedItem->id,
                'csr_stock_id' => $request->csr_stock_id,
                'ris_no' => $request->ris_no,
                'chrgcode' => $request->chrgcode,
                'cl2comb_before' => $request->cl2comb_before,
                'quantity_before' => $request->quantity_before,
                'cl2comb_after' => $request->cl2comb_before,
                'prev_qty' => 0,
                'new_qty' => $request->quantity_after,
                'supplierID' => $request->supplierID,
                'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
                'delivered_date' =>  Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
                'expiration_date' =>  Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v'),
                'action' => 'CONVERTED ITEM',
                'remarks' => '',
                'converted_by' => $converted_by,
            ]);
        } else {
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
                'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
                'delivered_date' =>  Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
                'expiration_date' =>  Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v'),
                'action' => 'CONVERTED ITEM',
                'remarks' => '',
                'converted_by' => $converted_by,
            ]);
        }

        return redirect()->back();
    }

    public function update(CsrItemConversion $convertitem, Request $request)
    {
        $updated_by = Auth::user()->employeeid;
        // dd($request);

        $convertedItem = CsrItemConversionLogs::where('item_conversion_id', $request->id)->first();
        // dd($convertedItem);

        $item_price = ItemPrices::where('ris_no', $request->ris_no)
            ->where('cl2comb', $request->cl2comb_before)
            ->first();

        // dd($item_price);

        CsrItemConversion::where('id', $request->id)
            ->update([
                // 'cl2comb_after' => $request->cl2comb_after == null ? $convertedItem->cl2comb_after : $request->cl2comb_after,
                'quantity_after' => $request->quantity_after,
                'updated_by' => $updated_by,
            ]);

        $price_per_unit = $item_price->hospital_price / $request->quantity_after;
        $price_per_unit = number_format((float)$price_per_unit, 2, '.', '');

        $update_item_price = ItemPrices::where('ris_no', $request->ris_no)
            ->where('cl2comb', $request->cl2comb_before)
            ->update([
                'price_per_unit' => $price_per_unit,
            ]);

        //////////////
        $convertedItemLog = CsrItemConversionLogs::create([
            'item_conversion_id' => $convertedItem->item_conversion_id,
            'csr_stock_id' => $convertedItem->csr_stock_id,
            'ris_no' => $convertedItem->ris_no,
            'chrgcode' => $convertedItem->chrgcode,
            'cl2comb_before' => $convertedItem->cl2comb_after,
            'quantity_before' => $convertedItem->new_qty,
            'cl2comb_after' => $convertedItem->cl2comb_after,
            'prev_qty' => $convertedItem->new_qty,
            'new_qty' => $request->quantity_after,
            'supplierID' => $convertedItem->supplierID,
            'manufactured_date' => Carbon::parse($convertedItem->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($convertedItem->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  Carbon::parse($convertedItem->expiration_date)->format('Y-m-d H:i:s.v'),
            'action' => 'UPDATE QTY',
            'remarks' => $request->remarks, // todo
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
