<?php

namespace App\Http\Controllers\Csr\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CsrStocks;
use App\Models\CsrStocksLogs;
use App\Models\FundSource;
use App\Models\Item;
use App\Models\RequestStocks;
use App\Models\Supplier;
use App\Models\TypeOfCharge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $pending_requests = RequestStocks::where('status', 'PENDING')->count();
        $cancelled_requests = RequestStocks::where('status', 'CANCELLED')->count();
        $completed_requests = RequestStocks::where('status', 'RECEIVED')->count();

        $about_to_expire = CsrStocks::with('itemDetail:cl2comb,cl2desc')
            ->orderBy('expiration_date', 'ASC')->limit(10)->get();

        return Inertia::render('Csr/Dashboard/Index', [
            'pending_requests' => $pending_requests,
            'cancelled_requests' => $cancelled_requests,
            'completed_requests' => $completed_requests,
            'about_to_expire' => $about_to_expire,
        ]);
    }

    public function store(Request $request)
    {
        $entry_by = Auth::user()->employeeid;

        return Redirect::route('csrdashboard.index');
    }

    // public function update(CsrStocks $csrdashboard, Request $request)
    // {
    //     // dd($csrdashboard);
    //     $entry_by = Auth::user()->employeeid;

    //     $request->validate([
    //         'ris_no' => 'required',
    //         'fund_source' => 'required',
    //         'cl2comb' => 'required',
    //         'quantity' => 'required|numeric|min:0',
    //         'delivered_date' => 'required',
    //         'expiration_date' => 'required',
    //         'remarks' => 'required'
    //     ]);

    //     $prevStockDetails = CsrStocks::where('id', $request->stockId)->first();

    //     $updated = $csrstock->update([
    //         'ris_no' => $request->ris_no,
    //         'suppcode' => $request->suppcode,
    //         'chrgcode' => $request->fund_source,
    //         'cl2comb' => $request->cl2comb,
    //         'uomcode' => $request->uomcode,
    //         'quantity' => $request->quantity,
    //         'manufactured_date' => $request->manufactured_date == null ? null : Carbon::parse($request->manufactured_date)->setTimezone('Asia/Manila'),
    //         'delivered_date' => $request->delivered_date == null ? null : Carbon::parse($request->delivered_date)->setTimezone('Asia/Manila'),
    //         'expiration_date' => $request->expiration_date == null ? null : Carbon::parse($request->expiration_date)->setTimezone('Asia/Manila'),
    //     ]);

    //     $stockLogs = CsrStocksLogs::create([
    //         'stock_id' => $prevStockDetails->id,
    //         'ris_no' => $prevStockDetails->ris_no,
    //         'suppcode' => $prevStockDetails->suppcode,
    //         'chrgcode' => $prevStockDetails->chrgcode,
    //         'cl2comb' => $prevStockDetails->cl2comb,
    //         'uomcode' => $prevStockDetails->uomcode,
    //         'prev_qty' => $prevStockDetails->quantity,
    //         'new_qty' => $request->quantity,
    //         'manufactured_date' => $prevStockDetails->manufactured_date,
    //         'delivered_date' => $prevStockDetails->delivered_date,
    //         'expiration_date' => $prevStockDetails->expiration_date,
    //         'action' => 'UPDATE',
    //         'remarks' => $request->remarks,
    //         'entry_by' => $entry_by,
    //     ]);

    //     return Redirect::route('csrdashboard.index');
    // }

    // public function destroy(CsrStocks $csrdashboard, Request $request)
    // {
    //     $request->validate([
    //         'remarks' => 'required'
    //     ]);

    //     $entry_by = Auth::user()->employeeid;

    //     $prevStockDetails = CsrStocks::where('id', $csrstock->id)->first();

    //     $csrstock->delete();

    //     $stockLogs = CsrStocksLogs::create([
    //         'stock_id' => $prevStockDetails->id,
    //         'ris_no' => $prevStockDetails->ris_no,
    //         'suppcode' => $prevStockDetails->suppcode,
    //         'chrgcode' => $prevStockDetails->chrgcode,
    //         'cl2comb' => $prevStockDetails->cl2comb,
    //         'uomcode' => $prevStockDetails->uomcode,
    //         'prev_qty' => $prevStockDetails->quantity,
    //         'new_qty' => $prevStockDetails->quantity,
    //         'manufactured_date' => $prevStockDetails->manufactured_date,
    //         'delivered_date' => $prevStockDetails->delivered_date,
    //         'expiration_date' => $prevStockDetails->expiration_date,
    //         'action' => 'DELETE',
    //         'remarks' => $request->remarks,
    //         'entry_by' => $entry_by,
    //     ]);

    //     return Redirect::route('csrdashboard.index');
    // }
}
