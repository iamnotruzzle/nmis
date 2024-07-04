<?php

namespace App\Http\Controllers\Csr\Utility\ManualAddStocks;

use App\Http\Controllers\Controller;
use App\Models\CsrItemConversion;
use App\Models\CsrStocks;
use App\Models\Item;
use App\Models\ItemPrices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManualAddStocksController extends Controller
{
    public function index()
    {
        //
    }
    public function store(Request $request)
    {
        // dd($request);

        $entry_by = Auth::user()->employeeid;

        $manufactured_date = Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v');
        $delivered_date = Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v');
        $expiration_date = Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v');

        $unit = Item::where('cl2comb', $request->cl2comb)->first('uomcode');

        $stock = CsrStocks::create([
            'ris_no' => $request->ris_no,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $unit->uomcode,
            'supplierID' => $request->supplier,
            'chrgcode' => $request->fund_source,
            'quantity' => $request->quantity,
            'manufactured_date' => $manufactured_date,
            'delivered_date' => $delivered_date,
            'expiration_date' => $expiration_date,
            'acquisition_price' => $request->acquisitionPrice,
            'converted' => 'y',
        ]);

        if ($request->cl2comb_after != null && $request->quantity_after != null) {

            if ($request->price_per_unit != null) {
                $itemPrices = ItemPrices::create([
                    'cl2comb' => $request->cl2comb_after,
                    'price_per_unit' => $request->price_per_unit,
                    'entry_by' => $entry_by,
                    'ris_no' => $request->ris_no,
                    'acquisition_price' => $request->acquisitionPrice,
                    'hospital_price' => $request->hospital_price,
                ]);
            }

            $convertedItem = CsrItemConversion::create([
                'csr_stock_id' => $stock->id,
                'ris_no' => $stock->ris_no,
                'chrgcode' => $stock->chrgcode,
                'cl2comb_before' => $stock->cl2comb,
                'quantity_before' => $stock->quantity,
                'cl2comb_after' => $request->cl2comb_after,
                'quantity_after' => $request->quantity_after,
                'supplierID' => $stock->supplierID,
                'manufactured_date' => Carbon::parse($stock->manufactured_date)->format('Y-m-d H:i:s.v'),
                'delivered_date' =>  Carbon::parse($stock->delivered_date)->format('Y-m-d H:i:s.v'),
                'expiration_date' =>  Carbon::parse($stock->expiration_date)->format('Y-m-d H:i:s.v'),
                'converted_by' => $entry_by,
            ]);
        }

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
