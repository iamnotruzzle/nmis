<?php

namespace App\Http\Controllers\Wards\ExistingStock;

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

class ExistingStockController extends Controller
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
            'cl2comb' => 'required',
            'quantity' => 'required',
            // 'delivered_date' => 'required',
        ]);

        // $item = Item::where('cl2comb', $request->cl2comb)->first();

        $currentItemPrice = DB::select(
            "SELECT TOP 1 * FROM csrw_item_prices WHERE cl2comb = ?
                AND item_conversion_id IS NOT NULL
                ORDER BY created_at DESC;",
            [$request->cl2comb]
        );
        // dd(empty($currentItemPrice));

        if (empty($currentItemPrice)) {
            session()->forget('noItemPrice'); // Remove previous value
            session(['noItemPrice' => 0]);
            session()->save();
            return redirect()->back();
        } else {
            $itemPrices = ItemPrices::create([
                'ris_no' => $tempRisNo,
                'cl2comb' => $request->cl2comb,
                'acquisition_price' => $request->price_per_unit,
                'hospital_price' =>  $request->price_per_unit,
                'price_per_unit' =>  $currentItemPrice[0]->price_per_unit,
                'entry_by' => $entry_by,
            ]);

            $existingStock = WardsStocks::create([
                'request_stocks_id' => null,
                'request_stocks_detail_id' => null,
                'ris_no' => $tempRisNo,
                'stock_id' => null,
                'is_consumable' => null,
                'location' => $request->authLocation,
                'cl2comb' => $request->cl2comb,
                'uomcode' => $request->uomcode,
                'chrgcode' => '8',
                'quantity' => $request->quantity,
                'from' => 'EXISTING_STOCKS',
                // 'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
                'delivered_date' =>  Carbon::now(),
                'expiration_date' =>  Carbon::maxValue(),
            ]);

            $wardStockLogs = WardsStocksLogs::create([
                'request_stocks_id' => null,
                'request_stocks_detail_id' => null,
                'ris_no' => $tempRisNo,
                'stock_id' => null,
                'wards_stocks_id' => $existingStock->id,
                'is_consumable' => null,
                'location' => $request->authLocation,
                'cl2comb' => $request->cl2comb,
                'uomcode' => $request->uomcode,
                'chrgcode' => '8',
                'prev_qty' => 0,
                'new_qty' => $request->quantity,
                'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
                'delivered_date' =>  Carbon::now(),
                'expiration_date' =>  Carbon::maxValue(),
                'action' => 'CREATE',
                'remarks' => null,
                'entry_by' => $entry_by,
            ]);

            return Redirect::route('requeststocks.index');
        }
    }


    public function update(WardsStocks $wardsstock, Request $request)
    {
        // dd($request);

        WardsStocks::where('id', $request->id)->update([
            'quantity' => $request->quantity,
        ]);

        return Redirect::route('requeststocks.index');
    }

    public function destroy($id)
    {
        //
    }
}
