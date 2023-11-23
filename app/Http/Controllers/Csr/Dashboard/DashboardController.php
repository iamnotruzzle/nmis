<?php

namespace App\Http\Controllers\Csr\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CsrStocksMedicalSupplies;
use App\Models\CsrStocksMedicalSuppliesLogs;
use App\Models\Item;
use App\Models\RequestStocks;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchString = $request->search;

        $pending_requests = RequestStocks::where('status', 'PENDING')->count();
        $cancelled_requests = RequestStocks::where('status', 'CANCELLED')->count();
        $completed_requests = RequestStocks::where('status', 'RECEIVED')->count();

        $about_to_expire = CsrStocksMedicalSupplies::with('itemDetail:cl2comb,cl2desc')
            ->orderBy('expiration_date', 'ASC')->limit(10)->get();

        $items = Item::with('unit')
            ->where('cl2stat', 'A')
            ->orderBy('cl2desc', 'ASC')
            ->get();

        $stocks = CsrStocksMedicalSupplies::with('unit:uomcode,uomdesc', 'itemDetail', 'supplierDetail:suppcode,suppname', 'brandDetail', 'typeOfCharge:chrgcode,chrgdesc', 'fundSource:id,fsid,fsName,cluster_code')
            ->whereHas('itemDetail', function ($q) use ($searchString) {
                $q->where('cl2desc', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('ris_no', 'LIKE', '%' . $searchString . '%');
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
            ->paginate(10);

        // brands
        $brands = Brand::get();

        return Inertia::render('Csr/Dashboard/Index', [
            'pending_requests' => $pending_requests,
            'cancelled_requests' => $cancelled_requests,
            'completed_requests' => $completed_requests,
            'about_to_expire' => $about_to_expire,
            'items' => $items,
            'stocks' => $stocks,
            'brands' => $brands,
        ]);
    }

    public function store(Request $request)
    {
        $entry_by = Auth::user()->employeeid;

        $request->validate([
            'ris_no' => 'required',
            'fund_source' => 'required',
            'cl2comb' => 'required',
            'brand' => 'required',
            'quantity' => 'required|numeric|min:0',
            'delivered_date' => 'required',
            'expiration_date' => 'required',
        ]);

        $stock = CsrStocksMedicalSupplies::create([
            'ris_no' => $request->ris_no,
            'suppcode' => $request->suppcode,
            'chrgcode' => $request->fund_source,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'brand' => $request->brand,
            'quantity' => $request->quantity,
            'manufactured_date' => $request->manufactured_date == null ? null : Carbon::parse($request->manufactured_date)->setTimezone('Asia/Manila'),
            'delivered_date' => $request->delivered_date == null ? null : Carbon::parse($request->delivered_date)->setTimezone('Asia/Manila'),
            'expiration_date' => $request->expiration_date == null ? null : Carbon::parse($request->expiration_date)->setTimezone('Asia/Manila'),
        ]);

        $stockLogs = CsrStocksMedicalSuppliesLogs::create([
            'stock_id' => $stock->id,
            'ris_no' => $stock->ris_no,
            'suppcode' => $stock->suppcode,
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

        return Redirect::route('csrdashboard.index');
    }

    // public function update(CsrStocksMedicalSupplies $csrdashboard, Request $request)
    // {
    //     // dd($csrdashboard);
    //     $entry_by = Auth::user()->employeeid;

    //     $request->validate([
    //         'ris_no' => 'required',
    //         'fund_source' => 'required',
    //         'cl2comb' => 'required',
    //         'brand' => 'required',
    //         'quantity' => 'required|numeric|min:0',
    //         'delivered_date' => 'required',
    //         'expiration_date' => 'required',
    //         'remarks' => 'required'
    //     ]);

    //     $prevStockDetails = CsrStocksMedicalSupplies::where('id', $request->stockId)->first();

    //     $updated = $csrstock->update([
    //         'ris_no' => $request->ris_no,
    //         'suppcode' => $request->suppcode,
    //         'chrgcode' => $request->fund_source,
    //         'cl2comb' => $request->cl2comb,
    //         'uomcode' => $request->uomcode,
    //         'brand' => $request->brand,
    //         'quantity' => $request->quantity,
    //         'manufactured_date' => $request->manufactured_date == null ? null : Carbon::parse($request->manufactured_date)->setTimezone('Asia/Manila'),
    //         'delivered_date' => $request->delivered_date == null ? null : Carbon::parse($request->delivered_date)->setTimezone('Asia/Manila'),
    //         'expiration_date' => $request->expiration_date == null ? null : Carbon::parse($request->expiration_date)->setTimezone('Asia/Manila'),
    //     ]);

    //     $stockLogs = CsrStocksMedicalSuppliesLogs::create([
    //         'stock_id' => $prevStockDetails->id,
    //         'ris_no' => $prevStockDetails->ris_no,
    //         'suppcode' => $prevStockDetails->suppcode,
    //         'chrgcode' => $prevStockDetails->chrgcode,
    //         'cl2comb' => $prevStockDetails->cl2comb,
    //         'uomcode' => $prevStockDetails->uomcode,
    //         'brand' => $prevStockDetails->brand,
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

    // public function destroy(CsrStocksMedicalSupplies $csrdashboard, Request $request)
    // {
    //     $request->validate([
    //         'remarks' => 'required'
    //     ]);

    //     $entry_by = Auth::user()->employeeid;

    //     $prevStockDetails = CsrStocksMedicalSupplies::where('id', $csrstock->id)->first();

    //     $csrstock->delete();

    //     $stockLogs = CsrStocksMedicalSuppliesLogs::create([
    //         'stock_id' => $prevStockDetails->id,
    //         'ris_no' => $prevStockDetails->ris_no,
    //         'suppcode' => $prevStockDetails->suppcode,
    //         'chrgcode' => $prevStockDetails->chrgcode,
    //         'cl2comb' => $prevStockDetails->cl2comb,
    //         'uomcode' => $prevStockDetails->uomcode,
    //         'brand' => $prevStockDetails->brand,
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
