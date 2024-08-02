<?php

namespace App\Http\Controllers\Wards\MedicalGases;

use App\Http\Controllers\Controller;
use App\Models\ItemPrices;
use App\Models\WardsStocks;
use App\Models\WardsStocksLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class WardMedicalGasesController extends Controller
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
            // 'expiration_date' => 'required',
        ]);

        $itemPrices = ItemPrices::create([
            'ris_no' => $tempRisNo,
            'cl2comb' => $request->cl2comb,
            'acquisition_price' => $request->acquisition_price,
            'hospital_price' => $request->hospital_price,
            'price_per_unit' => $request->price_per_unit,
            'entry_by' => $entry_by,
        ]);

        $medicalGases = WardsStocks::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'ris_no' => $tempRisNo,
            'stock_id' => null,
            'is_consumable' => 'y',
            'location' => $request->authLocation,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'chrgcode' => $request->fund_source,
            'quantity' => $request->quantity,
            'average' => $request->average,
            'total_usage' => (int)$request->quantity * (int)$request->average,
            'from' => 'MEDICAL GASES',
            // 'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  Carbon::maxValue(),
        ]);
        // dd($medicalGases);

        $wardStockLogs = WardsStocksLogs::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'ris_no' => $tempRisNo,
            'stock_id' => null,
            'is_consumable' => 'y',
            'location' => $request->authLocation,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'chrgcode' => $request->fund_source,
            'prev_qty' => 0,
            'new_qty' => $request->quantity,
            'average' => $request->average,
            'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  Carbon::maxValue(),
            'action' => 'CREATE',
            'remarks' => null,
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
