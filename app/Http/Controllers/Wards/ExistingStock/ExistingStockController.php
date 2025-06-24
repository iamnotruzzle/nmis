<?php

namespace App\Http\Controllers\Wards\ExistingStock;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemPrices;
use App\Models\WardConsumptionTracker;
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
            session()->forget('noItemPrice'); // Remove previous value
            session(['noItemPrice' => 1]);
            session()->save();

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
                'expiration_date' =>  $request->expiration_date ? Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v') : Carbon::maxValue(),
            ]);

            $price = DB::select(
                "SELECT price.id
                FROM csrw_wards_stocks as ward_stock
                JOIN csrw_item_prices as price ON price.cl2comb = ward_stock.cl2comb AND price.ris_no = ward_stock.ris_no
                WHERE ward_stock.cl2comb = ?
                AND ward_stock.ris_no = ?",
                [$existingStock->cl2comb, $existingStock->ris_no]
            );
            // dd($price[0]->id);

            $id = $existingStock->id;
            $item_conversion_id = $existingStock->stock_id;
            $ris_no = $existingStock->ris_no;
            $cl2comb = $existingStock->cl2comb;
            $uomcode = $existingStock->uomcode;
            $quantity = $existingStock->quantity;
            $location = $existingStock->location;
            $price_id = $price[0]->id;
            $from = $existingStock->from;

            $this->existingStockForTrackerLog(
                $id,
                $item_conversion_id,
                $ris_no,
                $cl2comb,
                $uomcode,
                $quantity,
                $location,
                $price_id,
                $from,
            );

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
                'expiration_date' =>  $request->expiration_date ? Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v') : Carbon::maxValue(),
                'action' => 'CREATE',
                'remarks' => null,
                'entry_by' => $entry_by,
            ]);

            return Redirect::route('wardinv.index');
        }
    }

    public function existingStockForTrackerLog(
        $id,
        $item_conversion_id,
        $ris_no,
        $cl2comb,
        $uomcode,
        $quantity,
        $location,
        $price_id,
        $from,
    ) {
        // Check if this stock already exists in the tracker with no end balance (meaning it's still in progress)
        $existingTracker = WardConsumptionTracker::where('ward_stock_id', $id)
            ->where('cl2comb', $cl2comb)
            ->where('price_id', $price_id)
            ->whereNull('end_bal_date')
            ->exists();

        if (!$existingTracker) {
            // New stock has been received after beginning balance, so create a new row
            WardConsumptionTracker::create([
                'ward_stock_id'    => $id,
                'item_conversion_id' => $item_conversion_id,
                'ris_no'           => $ris_no,
                'cl2comb'          => $cl2comb,
                'uomcode'          => $uomcode,
                'initial_qty'      => $quantity,
                'beg_bal_date'     => null, // intentionally left null
                'beg_bal_qty'      => 0, // intentionally left null
                'location'         => $location,
                'item_from'        => $from, // Whether it's from CSR or a ward
                'price_id'         => $price_id,
            ]);
        }
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
