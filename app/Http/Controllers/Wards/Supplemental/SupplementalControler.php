<?php

namespace App\Http\Controllers\Wards\Supplemental;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemPrices;
use App\Models\WardsStocks;
use App\Models\WardsStocksLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class SupplementalControler extends Controller
{
    public function index()
    {
        //
    }

    public function generateTempRisNo()
    {
        // Get the current year
        $currentYear = date("Y");

        // Extract the last two digits of the year (YY format)
        $yearDigits = substr($currentYear, -2);

        // Generate random numbers for the XXX part (3 digits)
        $randomPart1 = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

        // Generate random numbers for the XXXXX part (5 digits)
        $randomPart2 = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        // Create the final formatted string
        $randomNumber = "{$yearDigits}-{$randomPart1}-{$randomPart2}G";

        return $randomNumber;
    }

    public function store(Request $request)
    {
        // dd($request);
        $tempRisNo = $this->generateTempRisNo();

        $entry_by = Auth::user()->employeeid;

        $request->validate([
            'fund_source' => 'required',
            'cl2comb' => 'required',
            'quantity' => 'required',
            'delivered_date' => 'required',
            'price_per_unit' => 'required',
        ]);

        $item = Item::where('cl2comb', $request->cl2comb)->first();

        $itemPrices = ItemPrices::create([
            'ris_no' => $tempRisNo,
            'cl2comb' => $request->cl2comb,
            'acquisition_price' => $request->price_per_unit,
            'hospital_price' =>  $request->price_per_unit,
            'price_per_unit' =>  $request->price_per_unit,
            'entry_by' => $entry_by,
        ]);

        $supplementalItem = WardsStocks::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'ris_no' => $tempRisNo,
            'stock_id' => null,
            'is_consumable' => null,
            'location' => $request->authLocation,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'chrgcode' => $request->fund_source,
            'quantity' => $request->quantity,
            'from' => 'SUPPLEMENTAL',
            // 'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  $request->expiration_date ? Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v') : Carbon::maxValue(),
        ]);

        $wardStockLogs = WardsStocksLogs::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'ris_no' => $tempRisNo,
            'stock_id' => null,
            'wards_stocks_id' => $supplementalItem->id,
            'is_consumable' => null,
            'location' => $request->authLocation,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'chrgcode' => $request->fund_source,
            'prev_qty' => 0,
            'new_qty' => $request->quantity,
            'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  $request->expiration_date ? Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v') : Carbon::maxValue(),
            'action' => 'CREATE',
            'remarks' => null,
            'entry_by' => $entry_by,
        ]);

        return Redirect::route('wardinv.index');
    }


    public function update(WardsStocks $wardsstock, Request $request)
    {
        // dd($request);

        WardsStocks::where('id', $request->id)->update([
            'quantity' => $request->quantity,
        ]);

        return Redirect::route('wardinv.index');
    }

    public function destroy($id)
    {
        //
    }
}
