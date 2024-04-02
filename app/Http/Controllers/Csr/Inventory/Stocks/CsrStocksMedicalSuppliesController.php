<?php

namespace App\Http\Controllers\Csr\Inventory\Stocks;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CsrStocksMedicalSupplies;
use App\Models\CsrStocksMedicalSuppliesLogs;
use App\Models\FundSource;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\TypeOfCharge;
use Carbon\Carbon;
// use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use mysqli;

class CsrStocksMedicalSuppliesController extends Controller
{
    public function index(Request $request)
    {
        $items = DB::select(
            "SELECT hclass2.cl2comb as cl2comb, hclass2.cl2desc as cl2desc, huom.uomcode as uomcode, huom.uomdesc as uomdesc FROM hclass2
                JOIN huom ON hclass2.uomcode = huom.uomcode
                WHERE hclass2.cl1comb LIKE '1000-%'
                AND hclass2.cl2stat = 'A'
                ORDER BY hclass2.cl2desc ASC;
            ",
        );

        $stocks = DB::select(
            "SELECT medsupply.id, medsupply.ris_no,
                medsupply.suppcode, supplier.suppname,
                typeOfCharge.chrgcode as codeFromHCharge, typeOfCharge.chrgdesc as descFromHCharge,
                fundSource.fsid as codeFromFundSource, fundSource.fsName as descFromFundSource,
                medsupply.cl2comb, item.cl2desc,
                unit.uomcode, unit.uomdesc,
                brand.id as brand_id, brand.[name] as brand_name,
                medsupply.quantity,
                reoder_level.normal_stock as normal_stock, reoder_level.alert_stock, reoder_level.critical_stock,
                medsupply.manufactured_date, medsupply.delivered_date, expiration_date
            FROM csrw_csr_stocks_med_supp as medsupply
            JOIN hclass2 as item ON medsupply.cl2comb = item.cl2comb
            JOIN huom as unit ON medsupply.uomcode = unit.uomcode
            JOIN hsupplier as supplier ON medsupply.suppcode = supplier.suppcode
            JOIN csrw_brands as brand ON medsupply.brand = brand.id
            LEFT JOIN hcharge as typeOfCharge ON medsupply.chrgcode = typeOfCharge.chrgcode
            LEFT JOIN csrw_fund_source as fundSource ON medsupply.chrgcode = fundSource.fsid
            LEFT JOIN (
                SELECT TOP 1 r.cl2comb, r.normal_stock as normal_stock, r.alert_stock, r.critical_stock
                FROM csrw_item_reorder_level as r
                ORDER BY r.created_at DESC
            ) as reoder_level ON medsupply.cl2comb = reoder_level.cl2comb
            ORDER BY medsupply.ris_no ASC;"

        );
        //  ORDER BY medsupply.expiration_date ASC;"

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

        $suppliers = Supplier::where('suppstat', 'A')->orderBy('suppname', 'ASC')->get(['suppcode', 'suppname', 'suppstat']);

        return Inertia::render('Csr/Inventory/Stocks/Index', [
            'items' => $items,
            'stocks' => $stocks,
            'brands' => $brands,
            'totalStocks' => $totalStocks,
            'typeOfCharge' => $typeOfCharge,
            'fundSource' => $fundSource,
            'suppliers' => $suppliers,
        ]);
    }

    public function generateTempRisNo($length = 10)
    {
        return Str::random($length);
    }

    public function updateRisNo($currentRisNo, $newRisNo)
    {
        // dd($newRisNo);

        CsrStocksMedicalSupplies::where('ris_no', $currentRisNo)
            ->update([
                'ris_no' => $newRisNo
            ]);

        CsrStocksMedicalSuppliesLogs::where('ris_no', $currentRisNo)
            ->update([
                'ris_no' => $newRisNo
            ]);
    }

    public function store(Request $request)
    {
        if ($request->ris_no == null) {
            if ($request->newRisNo != null) {
                $this->updateRisNo($request->ris_no, $request->newRisNo);
            } else {
                $temp_ris_no = 'TEMP-RIS-' . $this->generateTempRisNo();

                $entry_by = Auth::user()->employeeid;

                foreach ($request->delivery_list as $delivery) {
                    // dd($delivery);
                    $stock = CsrStocksMedicalSupplies::create([
                        'ris_no' => $delivery['ris_no'] == null ? trim($temp_ris_no) : trim($request->ris_no),
                        'suppcode' => $delivery['supplier'],
                        'chrgcode' => $delivery['fundSource'],
                        'cl2comb' => $delivery['cl2comb'],
                        'uomcode' => $delivery['unit'],
                        'brand' => $delivery['brand'],
                        'quantity' => $delivery['quantity'],
                        'manufactured_date' => $delivery['manufactured_date'] == null ? null : Carbon::parse($delivery['manufactured_date'])->setTimezone('Asia/Manila'),
                        'delivered_date' => $delivery['delivered_date'] == null ? null : Carbon::parse($delivery['delivered_date'])->setTimezone('Asia/Manila'),
                        'expiration_date' => $delivery['expiration_date'] == null ? null : Carbon::parse($delivery['expiration_date'])->setTimezone('Asia/Manila'),
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
                }
            }

            return redirect()->back();
        } else {
            // $result = DB::connection('pims')->select(
            //     "SELECT *
            //     FROM tbl_ris_release
            //     WHERE risid = ?
            //     ORDER BY created_at ASC;",
            //     [$request->ris_no]
            // );

            $result = DB::connection('pims')->select(
                "SELECT ris_rel.risid, ris_rel.itemid, item.description, ris_rel.releaseqty, ris_rel.unitprice
                    FROM tbl_ris_release as ris_rel
                    JOIN tbl_items as item ON item.itemid = ris_rel.itemid
                    WHERE ris_rel.risid = ?;",
                [$request->ris_no]
            );

            return $result;
        }
    }

    public function update(CsrStocksMedicalSupplies $csrstock, Request $request)
    {
        // dd($request);

        $entry_by = Auth::user()->employeeid;

        // $request->validate([
        //     'fund_source' => 'required',
        //     'cl2comb' => 'required',
        //     'brand' => 'required',
        //     'quantity' => 'required|numeric|min:0',
        //     'delivered_date' => 'required',
        //     'expiration_date' => 'required',
        //     'remarks' => 'required'
        // ]);

        $prevStockDetails = CsrStocksMedicalSupplies::where('id', $csrstock->id)->first();

        $updated = $csrstock->update([
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
