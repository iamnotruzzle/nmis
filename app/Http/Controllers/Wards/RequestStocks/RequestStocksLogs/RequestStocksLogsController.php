<?php

namespace App\Http\Controllers\Wards\RequestStocks\RequestStocksLogs;

use App\Http\Controllers\Controller;
use App\Models\CsrItemConversion;
use App\Models\RequestStocksDetails;
use App\Models\ReturnedItems;
use App\Models\WardConsumptionTracker;
use App\Models\WardsStocks;
use App\Models\WardsStocksLogs;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class RequestStocksLogsController extends Controller
{
    public function index()
    {
        //
    }


    public function store(Request $request)
    {
        // dd($request);
        // $request->validate([
        //     'remarks' => 'required'
        // ]);

        $returned_by = Auth::user()->employeeid;
        // dd($auth->id);

        $entry_by = Auth::user()->employeeid;
        $ward_stock_id = $request->ward_stock_id;

        $previousQty = WardsStocks::where('id', $ward_stock_id)->first();
        // dd($previousQty->quantity);

        $updatedWardStock = WardsStocks::where('id', $ward_stock_id)
            ->update([
                'quantity' => $previousQty->quantity - $request->quantity,
            ]);

        $wardStock = WardsStocks::where('id', $request->ward_stock_id)->first();
        // dd($wardStock);
        // TODO: Create a disposal table

        ReturnedItems::create([
            'item_conversion_id' => $wardStock->stock_id,
            'ris_no' => $wardStock->ris_no,
            'cl2comb' => $wardStock->cl2comb,
            'from' => $wardStock->location,
            'returned_by' => $returned_by,
            'quantity' => $request->quantity,
            'remarks' => $request->remarks,
        ]);

        $returnQty = $request->quantity;

        $this->returnItemForTrackerLog(
            $ward_stock_id,
            $returnQty,
        );

        return redirect()->back();
    }

    public function returnItemForTrackerLog(
        $ward_stock_id,
        $returnQty,
    ) {
        $tracker = WardConsumptionTracker::where('ward_stock_id', $ward_stock_id)
            ->whereNull('end_bal_date')
            ->latest()
            ->first();

        if ($tracker) {
            $tracker->update([
                'return_to_csr_qty' => DB::raw("return_to_csr_qty + {$returnQty}")
            ]);
        }
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
