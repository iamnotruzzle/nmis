<?php

namespace App\Http\Controllers\Csr\Inventory\Stocks;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CsrStocks;
use App\Models\CsrStocksLogs;
use App\Models\Item;
use Carbon\Carbon;
// use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class CsrStocksController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;


        $items = Item::with('unit')
            ->where('cl2stat', 'A')
            ->orderBy('cl2desc', 'ASC')
            ->get();

        // dd($items);

        $stocks = CsrStocks::with('unit:uomcode,uomdesc', 'itemDetail', 'brandDetail', 'typeOfCharge:chrgcode,chrgdesc', 'fundSource:id,fsid,fsName,cluster_code')
            ->whereHas('itemDetail', function ($q) use ($searchString) {
                $q->where('cl2desc', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('batch_no', 'LIKE', '%' . $searchString . '%');
            })
            ->when(
                $request->from_md,
                function ($query, $value) {
                    $query->whereDate('manufactured_date', '>=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->to_md,
                function ($query, $value) {
                    $query->whereDate('manufactured_date', '<=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->from_dd,
                function ($query, $value) {
                    $query->whereDate('delivered_date', '>=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->to_dd,
                function ($query, $value) {
                    $query->whereDate('delivered_date', '<=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->from_ed,
                function ($query, $value) {
                    $query->whereDate('expiration_date', '>=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->to_ed,
                function ($query, $value) {
                    $query->whereDate('expiration_date', '<=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->orderBy('expiration_date', 'asc')
            ->paginate(15);

        // brands
        $brands = Brand::get();

        //////////////////////////////////////////////////
        $report = [];

        $csr_report = DB::table('csrw_csr_stocks')
            ->join('hclass2', 'csrw_csr_stocks.cl2comb', '=', 'hclass2.cl2comb')
            ->select('hclass2.cl2comb', 'hclass2.cl2desc', DB::raw('SUM(csrw_csr_stocks.quantity) as quantity'))
            // ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->groupBy('hclass2.cl2comb', 'hclass2.cl2desc')
            ->get();

        // dd($csr_report);
        //////////////////////////////////////////////////

        return Inertia::render('Csr/Inventory/Stocks/Index', [
            'items' => $items,
            'stocks' => $stocks,
            'brands' => $brands,
        ]);
    }

    public function store(Request $request)
    {
        // dd(Carbon::parse($request->expiration_date)->setTimezone('Asia/Manila'));

        // TODO make batch_no unique
        $entry_by = Auth::user()->employeeid;

        $request->validate([
            'batch_no' => 'required',
            'fund_source' => 'required',
            'cl2comb' => 'required',
            'brand' => 'required',
            'quantity' => 'required|numeric|min:0',
            'expiration_date' => 'required',
        ]);

        $stock = CsrStocks::create([
            'batch_no' => $request->batch_no,
            'chrgcode' => $request->fund_source,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'brand' => $request->brand,
            'quantity' => $request->quantity,
            'manufactured_date' => $request->manufactured_date == null ? null : Carbon::parse($request->manufactured_date)->setTimezone('Asia/Manila'),
            'delivered_date' => $request->delivered_date == null ? null : Carbon::parse($request->delivered_date)->setTimezone('Asia/Manila'),
            'expiration_date' => $request->expiration_date == null ? null : Carbon::parse($request->expiration_date)->setTimezone('Asia/Manila'),
        ]);

        $stockLogs = CsrStocksLogs::create([
            'stock_id' => $stock->id,
            'batch_no' => $stock->batch_no,
            'chrgcode' => $stock->chrgcode,
            'cl2comb' => $stock->cl2comb,
            'uomcode' => $stock->uomcode,
            'brand' => $stock->brand,
            'prev_qty' => 0,
            'new_qty' => $stock->quantity,
            'manufactured_date' => $stock->manufactured_date,
            'delivered_date' => $stock->delivered_date,
            'expiration_date' => $stock->expiration_date,
            'action' => 'CREATE',
            'remarks' => NULL,
            'entry_by' => $entry_by,
        ]);

        return Redirect::route('csrstocks.index');
    }

    public function update(CsrStocks $csrstock, Request $request)
    {
        // dd($request);

        $entry_by = Auth::user()->employeeid;

        $request->validate([
            'batch_no' => 'required',
            'fund_source' => 'required',
            'cl2comb' => 'required',
            'brand' => 'required',
            'quantity' => 'required|numeric|min:0',
            'expiration_date' => 'required',
            'remarks' => 'required'
        ]);

        $prevStockDetails = CsrStocks::where('id', $csrstock->id)->first();

        $updated = $csrstock->update([
            'batch_no' => $request->batch_no,
            'chrgcode' => $request->fund_source,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'brand' => $request->brand,
            'quantity' => $request->quantity,
            'manufactured_date' => $request->manufactured_date == null ? null : Carbon::parse($request->manufactured_date)->setTimezone('Asia/Manila'),
            'delivered_date' => $request->delivered_date == null ? null : Carbon::parse($request->delivered_date)->setTimezone('Asia/Manila'),
            'expiration_date' => $request->expiration_date == null ? null : Carbon::parse($request->expiration_date)->setTimezone('Asia/Manila'),
        ]);

        $stockLogs = CsrStocksLogs::create([
            'stock_id' => $prevStockDetails->id,
            'batch_no' => $prevStockDetails->batch_no,
            'chrgcode' => $prevStockDetails->chrgcode,
            'cl2comb' => $prevStockDetails->cl2comb,
            'uomcode' => $prevStockDetails->uomcode,
            'brand' => $prevStockDetails->brand,
            'prev_qty' => $prevStockDetails->quantity,
            'new_qty' => $request->quantity,
            'manufactured_date' => $prevStockDetails->manufactured_date,
            'delivered_date' => $prevStockDetails->delivered_date,
            'expiration_date' => $prevStockDetails->expiration_date,
            'action' => 'UPDATE',
            'remarks' => $request->remarks,
            'entry_by' => $entry_by,
        ]);


        return Redirect::route('csrstocks.index');
    }

    public function destroy(CsrStocks $csrstock, Request $request)
    {
        $request->validate([
            'remarks' => 'required'
        ]);

        $entry_by = Auth::user()->employeeid;

        $prevStockDetails = CsrStocks::where('id', $csrstock->id)->first();

        $csrstock->delete();

        $stockLogs = CsrStocksLogs::create([
            'stock_id' => $prevStockDetails->id,
            'batch_no' => $prevStockDetails->batch_no,
            'chrgcode' => $prevStockDetails->chrgcode,
            'cl2comb' => $prevStockDetails->cl2comb,
            'uomcode' => $prevStockDetails->uomcode,
            'brand' => $prevStockDetails->brand,
            'prev_qty' => $prevStockDetails->quantity,
            'new_qty' => $prevStockDetails->quantity,
            'manufactured_date' => $prevStockDetails->manufactured_date,
            'delivered_date' => $prevStockDetails->delivered_date,
            'expiration_date' => $prevStockDetails->expiration_date,
            'action' => 'DELETE',
            'remarks' => $request->remarks,
            'entry_by' => $entry_by,
        ]);

        return Redirect::route('csrstocks.index');
    }
}
