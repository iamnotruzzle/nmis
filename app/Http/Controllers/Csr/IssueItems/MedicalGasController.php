<?php

namespace App\Http\Controllers\Csr\IssueItems;

use App\Events\RequestStock;
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

class MedicalGasController extends Controller
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

        $item = Item::where('cl2comb', $request->cl2comb)->first();

        if ($item->itemcode == 'MSMG-1') {
            $itemPrices = ItemPrices::create([
                'ris_no' => $tempRisNo,
                'cl2comb' => $request->cl2comb,
                'acquisition_price' => 0.48,
                'hospital_price' =>  0.48,
                'price_per_unit' =>  0.48,
                'entry_by' => $entry_by,
            ]);
        } else if ($item->itemcode == 'MSMG-2') {
            $itemPrices = ItemPrices::create([
                'ris_no' => $tempRisNo,
                'cl2comb' => $request->cl2comb,
                'acquisition_price' => 0.35,
                'hospital_price' =>  0.35,
                'price_per_unit' =>  0.35,
                'entry_by' => $entry_by,
            ]);
        } else {
            $itemPrices = ItemPrices::create([
                'ris_no' => $tempRisNo,
                'cl2comb' => $request->cl2comb,
                'acquisition_price' => 0.43,
                'hospital_price' =>  0.43,
                'price_per_unit' =>  0.43,
                'entry_by' => $entry_by,
            ]);
        }


        $medicalGases = WardsStocks::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'ris_no' => $tempRisNo,
            'stock_id' => null,
            'is_consumable' => 'y',
            'location' => $request->wardcode,
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
            'wards_stocks_id' => $medicalGases->id,
            'is_consumable' => 'y',
            'location' => $request->wardcode,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'chrgcode' => $request->fund_source,
            'prev_qty' => 0,
            'new_qty' => $request->quantity,
            'average' => $request->average,
            'total_usage' => (int)$request->quantity * (int)$request->average,
            'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' =>  Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  Carbon::maxValue(),
            'action' => 'CREATE',
            'remarks' => null,
            'entry_by' => $entry_by,
        ]);

        return Redirect::route('issueitems.index');
    }

    public function update(WardsStocks $wardstock, Request $request)
    {
        // dd($request);

        $stock = DB::select(
            "SELECT * FROM csrw_wards_stocks WHERE id = $request->stock_id"
        );
        // dd($stock[0]);

        $average = (int)$stock[0]->average;
        $total_usage = (int)$stock[0]->total_usage;

        $updatedItem =  WardsStocks::where('id', $request->stock_id)
            ->update([
                'quantity' => (int)$stock[0]->quantity - (int)$request->quantity,
                'total_usage' => $total_usage - ((int)$request->quantity * $average)
            ]);

        $stock_logs = DB::select(
            "SELECT * FROM csrw_wards_stocks_logs WHERE wards_stocks_id = $request->stock_id"
        );
        // dd($stock_logs[0]);

        $logs_orig_quantity = (int)$stock_logs[0]->new_qty;
        $logs_total_usage = (int)$stock_logs[0]->total_usage;

        $updatedItemLogs =  WardsStocksLogs::where('wards_stocks_id', $stock_logs[0]->wards_stocks_id)
            ->update([
                'new_qty' => (int)$logs_orig_quantity - (int)$request->quantity,
                'total_usage' => $logs_total_usage - ((int)$request->quantity) * $average
            ]);

        return Redirect::route('issueitems.index');
    }

    public function destroy(Request $request)
    {

        return Redirect::route('issueitems.index');
    }
}
