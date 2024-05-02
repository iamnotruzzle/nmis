<?php

namespace App\Http\Controllers\Csr\Inventory\Stocks;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CsrStocks;
use App\Models\CsrStocksLogs;
use App\Models\FundSource;
use App\Models\Item;
use App\Models\ItemPrices;
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
use PDO;

class CsrStocksControllers extends Controller
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
            "SELECT stock.id, stock.ris_no,
                stock.suppcode, supplier.suppname,
                typeOfCharge.chrgcode as codeFromHCharge, typeOfCharge.chrgdesc as descFromHCharge,
                fundSource.fsid as codeFromFundSource, fundSource.fsName as descFromFundSource,
                stock.cl2comb, item.cl2desc, stock.acquisition_price, stock.mark_up, stock.selling_price,
                unit.uomcode, unit.uomdesc,
                brand.id as brand_id, brand.[name] as brand_name,
                stock.quantity,
                reoder_level.normal_stock as normal_stock, reoder_level.alert_stock, reoder_level.critical_stock,
                stock.manufactured_date, stock.delivered_date, expiration_date
            FROM csrw_csr_stocks as stock
            JOIN hclass2 as item ON stock.cl2comb = item.cl2comb
            JOIN huom as unit ON stock.uomcode = unit.uomcode
            JOIN hsupplier as supplier ON stock.suppcode = supplier.suppcode
            JOIN csrw_brands as brand ON stock.brand = brand.id
            LEFT JOIN hcharge as typeOfCharge ON stock.chrgcode = typeOfCharge.chrgcode
            LEFT JOIN csrw_fund_source as fundSource ON stock.chrgcode = fundSource.fsid
            LEFT JOIN (
                SELECT TOP 1 r.cl2comb, r.normal_stock as normal_stock, r.alert_stock, r.critical_stock
                FROM csrw_item_reorder_level as r
                ORDER BY r.created_at DESC
            ) as reoder_level ON stock.cl2comb = reoder_level.cl2comb
            ORDER BY stock.created_at ASC;"

        );
        //  ORDER BY medsupply.expiration_date ASC;"

        $totalStocks = DB::select(
            "SELECT item.cl2comb, item.cl2desc,  SUM(stock.quantity) as total_quantity
                FROM csrw_csr_stocks as stock
                JOIN hclass2 as item ON item.cl2comb = stock.cl2comb
                WHERE stock.quantity > 0
                GROUP BY item.cl2comb, item.cl2desc;
            "
        );

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

    public function store(Request $request)
    {
        if ($request->searchRis != null) {
            $result = array();

            $pims = DB::connection('pims')->select(
                "SELECT ris_rel.risid,
                ris_rel.itemid, item.description,
                ris_rel.fsid as fs_id, fs.fsName,
                ris_rel.releaseqty, ris_rel.unitprice
                    FROM tbl_ris_release as ris_rel
                    JOIN tbl_items as item ON item.itemid = ris_rel.itemid
                    JOIN tbl_ris as ris ON ris.risid = ris_rel.risid
                    JOIN tbl_fund_source as fs ON fs.fsid = ris_rel.fsid
                    WHERE  ris.officeID = 37
                    AND ris_rel.risid = ?
                    ORDER BY item.description ASC;",
                [$request->searchRis]
            );
            // ddd($pims);

            $items = DB::select(
                "SELECT * FROM hclass2
                    WHERE cl1comb LIKE '1000-%'
                    ORDER BY cl2desc;"
            );

            $units = DB::select(
                "SELECT * FROM huom
                    WHERE uomstat = 'A';"
            );

            foreach ($pims as $pim) {
                $matchedItem = null;

                // Loop through $items to find a match
                foreach ($items as $item) {
                    // Comparing the description and cl2desc
                    if ($pim->description == $item->cl2desc) {
                        $matchedItem = $item;
                        break;
                    }
                }

                // Check if a match was found
                if ($matchedItem) {
                    foreach ($units as $unit) {
                        if ($unit->uomcode == $matchedItem->uomcode) {
                            $result[] = [
                                'risid' => $pim->risid,
                                'cl2comb' => $matchedItem->cl2comb,
                                'cl2desc' => $matchedItem->cl2desc,
                                'fundSourceId' => $pim->fs_id,
                                'fundSourceName' => $pim->fsName,
                                'uomcode' => $matchedItem->uomcode,
                                'uomdesc' => $unit->uomdesc,
                                'releaseqty' => $pim->releaseqty,
                                'unitprice' => $pim->unitprice,
                            ];
                        }
                    }
                    // dd($result);
                }
            }

            return $result;
        } else {
            $deliveryDetails = $request->deliveryDetails;

            $entry_by = Auth::user()->employeeid;

            // dd($entry_by);

            foreach ($deliveryDetails as $r) {
                // dd($r['supplier']['suppcode']);
                $stock = CsrStocks::create([
                    'ris_no' => $r['risid'],
                    'cl2comb' => $r['cl2comb'],
                    'uomcode' => $r['uomcode'],
                    'suppcode' => $r['supplier']['suppcode'],
                    'brand' => $r['brand']['id'],
                    'chrgcode' => $r['fsid'],
                    'quantity' => $r['releaseqty'],
                    'manufactured_date' => $r['manufactured_date'],
                    'delivered_date' => $r['delivered_date'],
                    'expiration_date' => $r['expiration_date'],
                    'acquisition_price' => $r['unitprice'],
                    'mark_up' => $r['markupPercentage'],
                    'selling_price' => $r['sellingPrice'],
                ]);

                $stockLog = CsrStocksLogs::create([
                    'stock_id' => $stock->id,
                    'ris_no' => $r['risid'],
                    'cl2comb' => $r['cl2comb'],
                    'uomcode' => $r['uomcode'],
                    'suppcode' => $r['supplier']['suppcode'],
                    'brand' => $r['brand']['id'],
                    'chrgcode' => $r['fsid'],
                    'prev_qty' => 0,
                    'new_qty' => $r['releaseqty'],
                    'quantity' => $r['releaseqty'],
                    'manufactured_date' => $r['manufactured_date'],
                    'delivered_date' => $r['delivered_date'],
                    'expiration_date' => $r['expiration_date'],
                    'action' => 'ADDED DELIVERY',
                    'remarks' => '',
                    'acquisition_price' => $r['unitprice'],
                    'mark_up' => $r['markupPercentage'],
                    'selling_price' => $r['sellingPrice'],
                    'entry_by' => $entry_by,
                ]);

                // $itemPrices = ItemPrices::create([
                //     'cl2comb' => $request->cl2comb,
                //     'selling_price' => $request->selling_price,
                //     'entry_by' => $request->entry_by,
                // ]);
            }

            return redirect()->back();
        }
    }


    public function update(CsrStocks $csrstock, Request $request)
    {
        $entry_by = Auth::user()->employeeid;

        $request->validate([
            'suppcode' => 'required',
            'mark_up' => 'required',
            'expiration_date' => 'required',
            'remarks' => 'required'
        ]);

        $prevStockDetails = CsrStocks::where('id', $csrstock->id)->first();

        $updated = $csrstock->update([
            'suppcode' => $request->suppcode,
            'brand' => $request->brand,
            'quantity' => $request->quantity,
            'manufactured_date' => $request->manufactured_date,
            'delivered_date' => $request->delivered_date,
            'expiration_date' => $request->expiration_date,
            'mark_up' => $request->mark_up,
            'selling_price' => $request->selling_price,
            'remarks' => $request->remarks,
        ]);

        $stockLog = CsrStocksLogs::create([
            'stock_id' => $prevStockDetails->id,
            'ris_no' => $prevStockDetails->ris_no,
            'cl2comb' => $prevStockDetails->cl2comb,
            'uomcode' => $prevStockDetails->uomcode,
            'suppcode' => $prevStockDetails->suppcode,
            'brand' => $prevStockDetails->brand,
            'chrgcode' => $prevStockDetails->chrgcode,
            'prev_qty' => $prevStockDetails->quantity,
            'new_qty' => $request->quantity,
            'manufactured_date' => $request->manufactured_date,
            'delivered_date' => $request->delivered_date,
            'expiration_date' => $request->expiration_date,
            'action' => 'UPDATE DELIVERY',
            'remarks' => $request->remarks,
            'mark_up' => $request->mark_up,
            'selling_price' => $request->selling_price,
            'entry_by' => $entry_by,
        ]);

        return redirect()->back();
    }

    public function destroy(CsrStocks $csrstock, Request $request)
    {
        // $request->validate([
        //     'remarks' => 'required'
        // ]);

        // $entry_by = Auth::user()->employeeid;

        // $prevStockDetails = CsrStocks::where('id', $csrstock->id)->first();

        // $csrstock->delete();

        // $stockLogs = CsrStocksLogs::create([
        //     'stock_id' => $prevStockDetails->id,
        //     'ris_no' => $prevStockDetails->ris_no,
        //     'suppcode' => $prevStockDetails->suppcode,
        //     'chrgcode' => $prevStockDetails->chrgcode,
        //     'cl2comb' => $prevStockDetails->cl2comb,
        //     'uomcode' => $prevStockDetails->uomcode,
        //     'brand' => $prevStockDetails->brand,
        //     'prev_qty' => $prevStockDetails->quantity,
        //     'new_qty' => $prevStockDetails->quantity,
        //     'manufactured_date' => $prevStockDetails->manufactured_date,
        //     'delivered_date' => $prevStockDetails->delivered_date,
        //     'expiration_date' => $prevStockDetails->expiration_date,
        //     'action' => 'DELETE',
        //     'remarks' => $request->remarks,
        //     'entry_by' => $entry_by,
        // ]);

        // return redirect()->back();
    }
}
