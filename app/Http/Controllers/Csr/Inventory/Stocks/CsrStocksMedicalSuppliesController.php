<?php

namespace App\Http\Controllers\Csr\Inventory\Stocks;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CsrStocksMedicalSupplies;
use App\Models\CsrStocksMedicalSuppliesLogs;
use App\Models\FundSource;
use App\Models\Item;
use App\Models\TypeOfCharge;
use Carbon\Carbon;
// use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class CsrStocksMedicalSuppliesController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;


        $items = Item::with('unit')
            ->where('cl2stat', 'A')
            ->orderBy('cl2desc', 'ASC')
            ->get();

        $from_md = Carbon::parse($request->from_md)->startOfDay();
        $to_md = Carbon::parse($request->to_md)->endOfDay();
        $from_dd = Carbon::parse($request->from_dd)->startOfDay();
        $to_dd = Carbon::parse($request->to_dd)->endOfDay();
        $from_ed = Carbon::parse($request->from_ed)->startOfDay();
        $to_ed = Carbon::parse($request->to_ed)->endOfDay();

        $stocks = CsrStocksMedicalSupplies::with('unit:uomcode,uomdesc', 'itemDetail', 'supplierDetail:suppcode,suppname', 'brandDetail', 'typeOfCharge:chrgcode,chrgdesc', 'fundSource:id,fsid,fsName,cluster_code')
            ->whereHas('itemDetail', function ($q) use ($searchString) {
                $q->where('cl2desc', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('ris_no', 'LIKE', '%' . $searchString . '%');
            })
            ->when(
                $request->from_md,
                function ($query, $value) use ($from_md) {
                    $query->whereDate('manufactured_date', '>=', $from_md);
                }
            )
            ->when(
                $request->to_md,
                function ($query, $value) use ($to_md) {
                    $query->whereDate('manufactured_date', '<=', $to_md);
                }
            )
            ->when(
                $request->from_dd,
                function ($query, $value) use ($from_dd) {
                    $query->whereDate('delivered_date', '>=', $from_dd);
                }
            )
            ->when(
                $request->to_dd,
                function ($query, $value) use ($to_dd) {
                    $query->whereDate('delivered_date', '<=', $to_dd);
                }
            )
            ->when(
                $request->from_ed,
                function ($query, $value) use ($from_ed) {
                    $query->whereDate('expiration_date', '>=', $from_ed);
                }
            )
            ->when(
                $request->to_ed,
                function ($query, $value) use ($to_ed) {
                    $query->whereDate('expiration_date', '<=', $to_ed);
                }
            )
            ->orderBy('expiration_date', 'asc')
            ->paginate(15);

        $totalStocks = CsrStocksMedicalSupplies::with('itemDetail')
            ->where('expiration_date', '>', Carbon::now()->setTimezone('Asia/Manila'))
            ->groupBy('cl2comb')
            ->select('cl2comb', DB::raw('SUM(quantity) as total_quantity'))
            ->get();

        // brands
        $brands = Brand::get();

        $fundSource = FundSource::get(['id', 'fsid', 'fsName', 'cluster_code']);

        $typeOfCharge = TypeOfCharge::where('chrgstat', 'A')
            ->where('chrgtable', 'NONDR')
            ->get(['chrgcode', 'chrgdesc', 'bentypcod', 'chrgtable']);

        return Inertia::render('Csr/Inventory/Stocks/Index', [
            'items' => $items,
            'stocks' => $stocks,
            'brands' => $brands,
            'totalStocks' => $totalStocks,
            'typeOfCharge' => $typeOfCharge,
            'fundSource' => $fundSource,
        ]);
    }

    public function store(Request $request)
    {
        // dd(Carbon::parse($request->expiration_date)->setTimezone('Asia/Manila'));

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

        return redirect()->back();
    }

    public function update(CsrStocksMedicalSupplies $csrstock, Request $request)
    {
        // dd($request);

        $entry_by = Auth::user()->employeeid;

        $request->validate([
            'ris_no' => 'required',
            'fund_source' => 'required',
            'cl2comb' => 'required',
            'brand' => 'required',
            'quantity' => 'required|numeric|min:0',
            'delivered_date' => 'required',
            'expiration_date' => 'required',
            'remarks' => 'required'
        ]);

        $prevStockDetails = CsrStocksMedicalSupplies::where('id', $csrstock->id)->first();

        $updated = $csrstock->update([
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
            'stock_id' => $prevStockDetails->id,
            'ris_no' => $prevStockDetails->ris_no,
            'suppcode' => $prevStockDetails->suppcode,
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


        return redirect()->back();
    }

    public function destroy(CsrStocksMedicalSupplies $csrstock, Request $request)
    {
        $request->validate([
            'remarks' => 'required'
        ]);

        $entry_by = Auth::user()->employeeid;

        $prevStockDetails = CsrStocksMedicalSupplies::where('id', $csrstock->id)->first();

        $csrstock->delete();

        $stockLogs = CsrStocksMedicalSuppliesLogs::create([
            'stock_id' => $prevStockDetails->id,
            'ris_no' => $prevStockDetails->ris_no,
            'suppcode' => $prevStockDetails->suppcode,
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

        return redirect()->back();
    }
}
