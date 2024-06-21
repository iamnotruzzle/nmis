<?php

namespace App\Http\Controllers\Csr\Inventory\Stocks;

use App\Http\Controllers\Controller;
use App\Models\CsrStocks;
use App\Models\CsrStocksLogs;
use App\Models\FundSource;
use App\Models\Item;
use App\Models\ItemPrices;
use App\Models\PimsSupplier;
use App\Models\Supplier;
use App\Models\TypeOfCharge;
use Carbon\Carbon;
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
            "SELECT hclass2.cl1comb, hclass2.cl2comb as cl2comb, hclass2.cl2desc as cl2desc, huom.uomcode as uomcode, huom.uomdesc as uomdesc FROM hclass2
                JOIN huom ON hclass2.uomcode = huom.uomcode
                WHERE hclass2.cl1comb LIKE '1000-%'
                AND hclass2.cl2stat = 'A'
                ORDER BY hclass2.cl2desc ASC;
            ",
        );

        $stocks = DB::select(
            "SELECT stock.id, stock.ris_no,
                stock.supplierID, supplier.suppname,
                typeOfCharge.chrgcode as codeFromHCharge, typeOfCharge.chrgdesc as descFromHCharge,
                fundSource.fsid as codeFromFundSource, fundSource.fsName as descFromFundSource,
                stock.cl2comb, item.cl2desc, stock.acquisition_price,
                unit.uomcode, unit.uomdesc,
                stock.quantity,
                stock.manufactured_date, stock.delivered_date, expiration_date, stock.converted
            FROM csrw_csr_stocks as stock
            JOIN hclass2 as item ON stock.cl2comb = item.cl2comb
            JOIN huom as unit ON stock.uomcode = unit.uomcode
            JOIN csrw_suppliers as supplier ON stock.supplierID = supplier.supplierID
            LEFT JOIN hcharge as typeOfCharge ON stock.chrgcode = typeOfCharge.chrgcode
            LEFT JOIN csrw_fund_source as fundSource ON stock.chrgcode = fundSource.fsid
            ORDER BY stock.created_at ASC;"

        );

        $totalDeliveries = DB::select(
            "SELECT item.cl2comb, item.cl2desc,  SUM(stock.quantity) as total_quantity
                FROM csrw_csr_stocks as stock
                JOIN hclass2 as item ON item.cl2comb = stock.cl2comb
                WHERE stock.quantity > 0
                GROUP BY item.cl2comb, item.cl2desc;
            "
        );

        $totalConvertedItems = DB::select(
            "SELECT converted.id, converted.ris_no, fund_source.fsName, converted.cl2comb_after, item.cl2desc, converted.quantity_after, converted.expiration_date, employee.firstname, employee.lastname
                FROM csrw_csr_item_conversion as converted
                JOIN hclass2 as item ON item.cl2comb = converted.cl2comb_after
                JOIN huom as uom ON uom.uomcode = item.uomcode
                JOIN hpersonal as employee ON employee.employeeid = converted.converted_by
                JOIN csrw_fund_source as fund_source ON fund_source.fsid = converted.chrgcode;
            "
        );

        $convertedItems = DB::select(
            "SELECT cl1comb, cl2comb, cl2desc, uomcode FROM hclass2 ORDER BY cl2desc ASC;"
        );

        $fundSource = FundSource::orderBy('fsName')
            ->get(['id', 'fsid', 'fsName', 'cluster_code']);

        // $typeOfCharge = TypeOfCharge::where('chrgstat', 'A')
        //     ->where('chrgtable', 'NONDR')
        //     ->get(['chrgcode', 'chrgdesc', 'bentypcod', 'chrgtable']);

        $suppliers = PimsSupplier::where('status', 'A')->orderBy('suppname', 'ASC')->get();

        return Inertia::render('Csr/Inventory/Stocks/Index', [
            'items' => $items,
            'stocks' => $stocks,
            'totalDeliveries' => $totalDeliveries,
            // 'typeOfCharge' => $typeOfCharge,
            'fundSource' => $fundSource,
            'suppliers' => $suppliers,
            'convertedItems' => $convertedItems,
            'totalConvertedItems' => $totalConvertedItems,
        ]);
    }

    public function generateTempRisNo($length = 10)
    {
        return Str::random($length);
    }

    public function store(Request $request)
    {
        // dd($request);
        if ($request->searchRis != null) {
            $result = array();

            // sample RIS NO 24-05-0246
            $pims = DB::connection('pims')->select(
                "SELECT ris_rel.risid, ris.risno,
                ris_rel.itemid, item.itemcode, item.description,
                ris_rel.fsid as fs_id, fs.fsName,
                ris_rel.releaseqty, ris_rel.unitprice
                    FROM tbl_ris_release as ris_rel
                    JOIN tbl_items as item ON item.itemid = ris_rel.itemid
                    JOIN tbl_ris as ris ON ris.risid = ris_rel.risid
                    JOIN tbl_fund_source as fs ON fs.fsid = ris_rel.fsid
                    WHERE  ris.officeID = 37
                    AND ris.risno = ?
                    ORDER BY item.description ASC;",
                [$request->searchRis]
            );
            // dd($pims);

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

                // OLD ITEM MATCH CHECKER
                // Loop through $items to find a match
                // foreach ($items as $item) {
                //     // Comparing the description and cl2desc
                //     if ($pim->description == $item->cl2desc) {
                //         $matchedItem = $item;
                //         break;
                //     }
                // }

                // Loop through $items to find a match
                foreach ($items as $item) {
                    // Comparing the description and cl2desc
                    if ($pim->itemcode == $item->itemcode) {
                        $matchedItem = $item;
                        break;
                    }
                }

                // Check if a match was found
                if ($matchedItem) {
                    foreach ($units as $unit) {
                        if ($unit->uomcode == $matchedItem->uomcode) {
                            $result[] = [
                                'risno' => $pim->risno,
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
                }
            }

            return $result;
        } else {
            $deliveryDetails = $request->deliveryDetails;

            $entry_by = Auth::user()->employeeid;

            foreach ($deliveryDetails as $r) {
                $stock = CsrStocks::create([
                    'ris_no' => $r['risno'],
                    'cl2comb' => $r['cl2comb'],
                    'uomcode' => $r['uomcode'],
                    'supplierID' => $r['supplier']['supplierID'],
                    'chrgcode' => $r['fsid'],
                    'quantity' => $r['releaseqty'],
                    'manufactured_date' => Carbon::parse($r['manufactured_date'])->format('Y-m-d H:i:s.v'),
                    'delivered_date' => Carbon::parse($r['delivered_date'])->format('Y-m-d H:i:s.v'),
                    'expiration_date' => Carbon::parse($r['expiration_date'])->format('Y-m-d H:i:s.v'),
                    'acquisition_price' => $r['unitprice'],
                    'converted' => 'n',
                ]);

                $stockLog = CsrStocksLogs::create([
                    'stock_id' => $stock->id,
                    'ris_no' => $r['risno'],
                    'cl2comb' => $r['cl2comb'],
                    'uomcode' => $r['uomcode'],
                    'supplierID' => $r['supplier']['supplierID'],
                    'chrgcode' => $r['fsid'],
                    'prev_qty' => 0,
                    'new_qty' => $r['releaseqty'],
                    'quantity' => $r['releaseqty'],
                    'manufactured_date' => Carbon::parse($r['manufactured_date'])->format('Y-m-d H:i:s.v'),
                    'delivered_date' => Carbon::parse($r['delivered_date'])->format('Y-m-d H:i:s.v'),
                    'expiration_date' => Carbon::parse($r['expiration_date'])->format('Y-m-d H:i:s.v'),
                    'action' => 'ADDED DELIVERY',
                    'remarks' => '',
                    'acquisition_price' => $r['unitprice'],
                    'entry_by' => $entry_by,
                    'converted' => 'n',
                ]);
            }

            return redirect()->back();
        }
    }


    public function update(CsrStocks $csrstock, Request $request)
    {
        $entry_by = Auth::user()->employeeid;

        $request->validate([
            'supplierID' => 'required',
            'expiration_date' => 'required',
            'remarks' => 'required'
        ]);

        $prevStockDetails = CsrStocks::where('id', $csrstock->id)->first();

        $updated = $csrstock->update([
            'supplierID' => $request->supplierID,
            'quantity' => $request->quantity,
            'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' => Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' => Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v'),
            'remarks' => $request->remarks,
        ]);

        $stockLog = CsrStocksLogs::create([
            'stock_id' => $prevStockDetails->id,
            'ris_no' => $prevStockDetails->ris_no,
            'cl2comb' => $prevStockDetails->cl2comb,
            'uomcode' => $prevStockDetails->uomcode,
            'supplierID' => $prevStockDetails->supplierID,
            'chrgcode' => $prevStockDetails->chrgcode,
            'prev_qty' => $prevStockDetails->quantity,
            'new_qty' => $request->quantity,
            'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' => Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' => Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v'),
            'action' => 'UPDATE DELIVERY',
            'remarks' => $request->remarks,
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
        //     'supplierID' => $prevStockDetails->supplierID,
        //     'chrgcode' => $prevStockDetails->chrgcode,
        //     'cl2comb' => $prevStockDetails->cl2comb,
        //     'uomcode' => $prevStockDetails->uomcode,
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
