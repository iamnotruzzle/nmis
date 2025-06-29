<?php

namespace App\Http\Controllers\Csr\Inventory\Stocks;

use App\Http\Controllers\Controller;
use App\Models\CsrItemConversion;
use App\Models\CsrItemConversionLogs;
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
use App\Models\Sessions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use mysqli;
use PDO;

class CsrStocksControllers extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        //#region ris_generator
        $currentYear = now()->format('y'); // Get last 2 digits of the year
        $currentMonth = now()->format('m'); // Get the month (2 digits)

        // Check if there are any records with the new format
        $latestRis = DB::table('csrw_csr_stocks')
            ->whereRaw("ris_no LIKE ?", ["$currentYear-$currentMonth-%"])
            ->orderBy('created_at', 'desc')
            ->value('ris_no');

        // Determine the next sequence number
        if ($latestRis) {
            $lastNumber = (int)substr($latestRis, -6); // Extract last 6 digits
            $nextNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '000001'; // Reset every new month
        }
        // Generate the new ris_no in the new format
        $newRisNo = "$currentYear-$currentMonth-$nextNumber";
        //#endregion

        $items = DB::select(
            "SELECT hclass2.cl1comb, hclass2.cl2comb as cl2comb, hclass2.cl2desc as cl2desc, huom.uomcode as uomcode, huom.uomdesc as uomdesc FROM hclass2
                JOIN huom ON hclass2.uomcode = huom.uomcode
                WHERE hclass2.cl1comb LIKE '1000-%'
                AND hclass2.cl2stat = 'A'
                ORDER BY hclass2.cl2desc ASC;
            ",
        );

        #region auth ward code and ward location type
        $authWardcode = DB::select(
            "SELECT TOP 1
                l.wardcode
                FROM
                    user_acc u
                INNER JOIN
                    csrw_login_history l ON u.employeeid = l.employeeid
                WHERE
                    l.employeeid = ?
                ORDER BY
                    l.created_at DESC;
                ",
            [Auth::user()->employeeid]
        );
        $authCode = $authWardcode[0]->wardcode;

        $locationType_query = DB::select("SELECT enctype FROM hward WHERE wardcode = ?;", [$authCode]);
        // $locationType_cached = Cache::get($enctype);
        $enctype = !empty($locationType_query) ? $locationType_query[0]->enctype : null;
        // Retrieve the location type from cache again in case it was just set
        #endregion

        // without pagination
        // Define cache keys for patient data and latest update timestamp
        // $cachedKeyCsrStocks = 'c_csr_stocks_' . $authCode;
        // $cacheKeyLatestUpdate = 'latest_update_' . $authCode;
        // // Attempt to retrieve cached patient data
        // $stocks = Cache::get($cachedKeyCsrStocks);
        // // If location type is null, fetch the latest created_at
        // if ($enctype === null) {
        //     $latestUpdatedAt = DB::select(
        //         "SELECT MAX(updated_at) as updated_at FROM csrw_csr_stocks;"
        //     );

        //     // Extract the latest updated_at from query result
        //     $latestUpdatedAt = $latestUpdatedAt[0]->updated_at ?? null;

        //     // Retrieve the cached latest update timestamp
        //     $cachedUpdatedAt = Cache::get($cacheKeyLatestUpdate);

        //     // If the latest updated_at has changed, fetch stocks data and update the cache
        //     if (!$cachedUpdatedAt || $latestUpdatedAt !== $cachedUpdatedAt) {
        //         $fetchedStocks = DB::select(
        //             // new and fixed
        //             "SELECT stock.id, stock.ris_no,
        //                 stock.supplierID, supplier.suppname,
        //                 fundSource.fsid as codeFromFundSource, fundSource.fsName as descFromFundSource,
        //                 stock.cl2comb, item.cl2desc, stock.acquisition_price,
        //                 unit.uomcode, unit.uomdesc,
        //                 stock.quantity, stock.delivered_date, expiration_date, stock.converted, stock.created_at
        //             FROM csrw_csr_stocks as stock
        //             JOIN hclass2 as item ON stock.cl2comb = item.cl2comb
        //             JOIN huom as unit ON stock.uomcode = unit.uomcode
        //             LEFT JOIN csrw_suppliers as supplier ON stock.supplierID = supplier.supplierID
        //             LEFT JOIN csrw_fund_source as fundSource ON stock.chrgcode = fundSource.fsid
        //             ORDER BY stock.created_at ASC;"
        //         );

        //         Cache::put($cacheKeyLatestUpdate, $latestUpdatedAt, now()->addMinutes(30));
        //         Cache::put($cachedKeyCsrStocks, $fetchedStocks, now()->addMinutes(30));
        //         $stocks = $fetchedStocks;
        //     } else {
        //         // Retrieve stocks data from cache if created_at has not changed
        //         $stocks = Cache::get($cachedKeyCsrStocks);
        //     }
        // }

        // with pagination
        if ($enctype === null) {
            $stocks = DB::table('csrw_csr_stocks as stock')
                ->select(
                    'stock.id',
                    'stock.ris_no',
                    'stock.supplierID',
                    'supplier.suppname',
                    'fundSource.fsid as codeFromFundSource',
                    'fundSource.fsName as descFromFundSource',
                    'stock.cl2comb',
                    'item.cl2desc',
                    'stock.acquisition_price',
                    'unit.uomcode',
                    'unit.uomdesc',
                    'stock.quantity',
                    'stock.delivered_date',
                    'expiration_date',
                    'stock.converted',
                    'stock.created_at'
                )
                ->join('hclass2 as item', 'stock.cl2comb', '=', 'item.cl2comb')
                ->join('huom as unit', 'stock.uomcode', '=', 'unit.uomcode')
                ->leftJoin('csrw_suppliers as supplier', 'stock.supplierID', '=', 'supplier.supplierID')
                ->leftJoin('csrw_fund_source as fundSource', 'stock.chrgcode', '=', 'fundSource.fsid')
                ->when($search, function ($query, $search) {
                    $query->where('item.cl2desc', 'like', "%{$search}%");
                })
                ->orderBy('stock.created_at', 'DESC')
                ->paginate(10); // You can change 10 to however many items you want per page
        }

        $totalDeliveries = DB::select(
            "SELECT stock.id as stock_id, stock.ris_no, item.cl2comb, item.cl2desc, stock.supplierID,
                stock.acquisition_price, price.hospital_price, price.price_per_unit, stock.chrgcode, stock.quantity, stock.manufactured_date,
                stock.delivered_date, stock.expiration_date, stock.converted
                FROM csrw_csr_stocks as stock
                JOIN hclass2 as item ON item.cl2comb = stock.cl2comb
                LEFT JOIN csrw_item_prices as price ON price.cl2comb = stock.cl2comb
                WHERE stock.quantity > 0;
            "
        );

        $totalConvertedItems = DB::select(
            "SELECT
                converted.id,
                converted.ris_no,
                fund_source.fsid,
                fund_source.fsName,
                converted.cl2comb_after,
                item_after.cl2desc AS cl2desc_after,
                converted.cl2comb_before,
                item_before.cl2desc AS cl2desc_before,
                converted.quantity_after,
                converted.total_issued_qty,
                price.price_per_unit,
                converted.expiration_date,
                employee.firstname,
                employee.lastname
            FROM
                csrw_csr_item_conversion AS converted
            JOIN
                hclass2 AS item_after ON item_after.cl2comb = converted.cl2comb_after
            JOIN
                hclass2 AS item_before ON item_before.cl2comb = converted.cl2comb_before
            JOIN
                huom AS uom ON uom.uomcode = item_after.uomcode
            JOIN
                hpersonal AS employee ON employee.employeeid = converted.converted_by
            JOIN
                csrw_fund_source AS fund_source ON fund_source.fsid = converted.chrgcode
            JOIN
                csrw_item_prices as price ON item_conversion_id = converted.id;"
        );
        // dd($totalConvertedItems);

        $cache_suppliers = 'c_suppliers_' . Auth::user()->employeeid;

        $suppliers = Cache::get($cache_suppliers);
        // dd($suppliers);

        return Inertia::render('Csr/Inventory/Stocks/Index', [
            'items' => $items,
            'stocks' => $stocks,
            'totalDeliveries' => $totalDeliveries,
            'newRisNo' => $newRisNo,
            'totalConvertedItems' => $totalConvertedItems,
            'suppliers' => $suppliers,
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
            // return redirect()->back();
            // return redirect()->back()->with('result', $result);
        } else {
            // dd($request);
            $deliveryDetails = $request->deliveryDetails;
            // dd($deliveryDetails);

            $entry_by = Auth::user()->employeeid;

            DB::beginTransaction();

            try {
                foreach ($deliveryDetails as $r) {
                    if (isset($r['cl2comb_after']) && isset($r['quantity_after'])) {
                        $stock = CsrStocks::create([
                            'ris_no' => $r['risno'],
                            'cl2comb' => $r['cl2comb'],
                            'uomcode' => $r['uomcode'],
                            'supplierID' => $r['supplier'] != null ? $r['supplier']['supplierID'] : null,
                            'chrgcode' => $r['fsid'],
                            'quantity' => $r['releaseqty'],
                            'delivered_date' => Carbon::parse($r['delivered_date'])->format('Y-m-d H:i:s.v'),
                            'expiration_date' => Carbon::parse($r['expiration_date'])->format('Y-m-d H:i:s.v'),
                            'acquisition_price' => $r['unitprice'],
                            'converted' => 'y',
                        ]);

                        $stockLog = CsrStocksLogs::create([
                            'stock_id' => $stock->id,
                            'ris_no' => $r['risno'],
                            'cl2comb' => $r['cl2comb'],
                            'uomcode' => $r['uomcode'],
                            'supplierID' => $r['supplier'] != null ? $r['supplier']['supplierID'] : null,
                            'chrgcode' => $r['fsid'],
                            'prev_qty' => 0,
                            'new_qty' => $r['releaseqty'],
                            'quantity' => $r['releaseqty'],
                            'delivered_date' => Carbon::parse($r['delivered_date'])->format('Y-m-d H:i:s.v'),
                            'expiration_date' => Carbon::parse($r['expiration_date'])->format('Y-m-d H:i:s.v'),
                            'action' => 'ADDED DELIVERY',
                            'remarks' => '',
                            'acquisition_price' => $r['unitprice'],
                            'entry_by' => $entry_by,
                            'converted' => 'y',
                        ]);

                        $convertedItem = CsrItemConversion::create([
                            'csr_stock_id' => $stock->id,
                            'ris_no' => $stock->ris_no,
                            'chrgcode' => $stock->chrgcode,
                            'cl2comb_before' => $stock->cl2comb,
                            'quantity_before' => $stock->quantity,
                            'cl2comb_after' => $r['cl2comb_after'],
                            'quantity_after' => $r['quantity_after'],
                            'supplierID' => $stock->supplierID,
                            'delivered_date' =>  Carbon::parse($stock->delivered_date)->format('Y-m-d H:i:s.v'),
                            'expiration_date' =>  Carbon::parse($stock->expiration_date)->format('Y-m-d H:i:s.v'),
                            'converted_by' => $entry_by,
                        ]);

                        if ($r['price_per_unit'] != null) {
                            $itemPrices = ItemPrices::create([
                                'cl2comb' => $r['cl2comb_after'],
                                'price_per_unit' => $r['price_per_unit'],
                                'entry_by' => $entry_by,
                                'ris_no' => $r['risno'],
                                'acquisition_price' => $r['unitprice'],
                                'hospital_price' => $r['hospital_price'],
                                'item_conversion_id' => $convertedItem->id,
                            ]);
                        }

                        $convertedItemLog = CsrItemConversionLogs::create([
                            'item_conversion_id' => $convertedItem->id,
                            'csr_stock_id' => $stock->id,
                            'ris_no' => $stock->ris_no,
                            'chrgcode' => $stock->chrgcode,
                            'cl2comb_before' => $stock->cl2comb,
                            'quantity_before' => $stock->quantity,
                            'cl2comb_after' => $r['cl2comb_after'],
                            'prev_qty' => 0,
                            'new_qty' => $r['quantity_after'],
                            'supplierID' => $stock->supplierID,
                            'delivered_date' =>  Carbon::parse($stock->delivered_date)->format('Y-m-d H:i:s.v'),
                            'expiration_date' =>  Carbon::parse($stock->expiration_date)->format('Y-m-d H:i:s.v'),
                            'action' => 'CONVERTED ITEM',
                            'remarks' => '',
                            'converted_by' => $entry_by,
                        ]);
                    } else {
                        $stock = CsrStocks::create([
                            'ris_no' => $r['risno'],
                            'cl2comb' => $r['cl2comb'],
                            'uomcode' => $r['uomcode'],
                            'supplierID' => $r['supplier'] != null ? $r['supplier']['supplierID'] : null,
                            'chrgcode' => $r['fsid'],
                            'quantity' => $r['releaseqty'],
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
                            'supplierID' => $r['supplier'] != null ? $r['supplier']['supplierID'] : null,
                            'chrgcode' => $r['fsid'],
                            'prev_qty' => 0,
                            'new_qty' => $r['releaseqty'],
                            'quantity' => $r['releaseqty'],
                            'delivered_date' => Carbon::parse($r['delivered_date'])->format('Y-m-d H:i:s.v'),
                            'expiration_date' => Carbon::parse($r['expiration_date'])->format('Y-m-d H:i:s.v'),
                            'action' => 'ADDED DELIVERY',
                            'remarks' => '',
                            'acquisition_price' => $r['unitprice'],
                            'entry_by' => $entry_by,
                            'converted' => 'n',
                        ]);
                    }
                }

                DB::commit(); // ✅ commit only if all pass
            } catch (\Exception $e) {
                DB::rollBack(); // ❌ rollback everything if any error occurs
                throw $e; // Optional: rethrow or handle the error as needed
            }

            // foreach ($deliveryDetails as $r) {
            //     if (isset($r['cl2comb_after']) && isset($r['quantity_after'])) {
            //         // dd($r);
            //         $stock = CsrStocks::create([
            //             'ris_no' => $r['risno'],
            //             'cl2comb' => $r['cl2comb'],
            //             'uomcode' => $r['uomcode'],
            //             'supplierID' => $r['supplier'] != null ? $r['supplier']['supplierID'] : null,
            //             'chrgcode' => $r['fsid'],
            //             'quantity' => $r['releaseqty'],
            //             // 'manufactured_date' => Carbon::parse($r['manufactured_date'])->format('Y-m-d H:i:s.v'),
            //             'delivered_date' => Carbon::parse($r['delivered_date'])->format('Y-m-d H:i:s.v'),
            //             'expiration_date' => Carbon::parse($r['expiration_date'])->format('Y-m-d H:i:s.v'),
            //             'acquisition_price' => $r['unitprice'],
            //             'converted' => 'y',
            //         ]);

            //         $stockLog = CsrStocksLogs::create([
            //             'stock_id' => $stock->id,
            //             'ris_no' => $r['risno'],
            //             'cl2comb' => $r['cl2comb'],
            //             'uomcode' => $r['uomcode'],
            //             'supplierID' => $r['supplier'] != null ? $r['supplier']['supplierID'] : null,
            //             'chrgcode' => $r['fsid'],
            //             'prev_qty' => 0,
            //             'new_qty' => $r['releaseqty'],
            //             'quantity' => $r['releaseqty'],
            //             // 'manufactured_date' => Carbon::parse($r['manufactured_date'])->format('Y-m-d H:i:s.v'),
            //             'delivered_date' => Carbon::parse($r['delivered_date'])->format('Y-m-d H:i:s.v'),
            //             'expiration_date' => Carbon::parse($r['expiration_date'])->format('Y-m-d H:i:s.v'),
            //             'action' => 'ADDED DELIVERY',
            //             'remarks' => '',
            //             'acquisition_price' => $r['unitprice'],
            //             'entry_by' => $entry_by,
            //             'converted' => 'y',
            //         ]);

            //         $convertedItem = CsrItemConversion::create([
            //             'csr_stock_id' => $stock->id,
            //             'ris_no' => $stock->ris_no,
            //             'chrgcode' => $stock->chrgcode,
            //             'cl2comb_before' => $stock->cl2comb,
            //             'quantity_before' => $stock->quantity,
            //             'cl2comb_after' => $r['cl2comb_after'],
            //             'quantity_after' => $r['quantity_after'],
            //             'supplierID' => $stock->supplierID,
            //             // 'manufactured_date' => Carbon::parse($stock->manufactured_date)->format('Y-m-d H:i:s.v'),
            //             'delivered_date' =>  Carbon::parse($stock->delivered_date)->format('Y-m-d H:i:s.v'),
            //             'expiration_date' =>  Carbon::parse($stock->expiration_date)->format('Y-m-d H:i:s.v'),
            //             'converted_by' => $entry_by,
            //         ]);
            //         //

            //         if ($r['price_per_unit'] != null) {
            //             $itemPrices = ItemPrices::create([
            //                 'cl2comb' => $r['cl2comb_after'],
            //                 'price_per_unit' => $r['price_per_unit'],
            //                 'entry_by' => $entry_by,
            //                 'ris_no' => $r['risno'],
            //                 'acquisition_price' => $r['unitprice'],
            //                 'hospital_price' => $r['hospital_price'],
            //                 'item_conversion_id' => $convertedItem->id,
            //             ]);
            //         }

            //         $convertedItemLog = CsrItemConversionLogs::create([
            //             'item_conversion_id' => $convertedItem->id,
            //             'csr_stock_id' => $stock->id,
            //             'ris_no' => $stock->ris_no,
            //             'chrgcode' => $stock->chrgcode,
            //             'cl2comb_before' => $stock->cl2comb,
            //             'quantity_before' => $stock->quantity,
            //             'cl2comb_after' => $r['cl2comb_after'],
            //             'prev_qty' => 0,
            //             'new_qty' => $r['quantity_after'],
            //             'supplierID' => $stock->supplierID,
            //             // 'manufactured_date' => Carbon::parse($stock->manufactured_date)->format('Y-m-d H:i:s.v'),
            //             'delivered_date' =>  Carbon::parse($stock->delivered_date)->format('Y-m-d H:i:s.v'),
            //             'expiration_date' =>  Carbon::parse($stock->expiration_date)->format('Y-m-d H:i:s.v'),
            //             'action' => 'CONVERTED ITEM',
            //             'remarks' => '',
            //             'converted_by' => $entry_by,
            //         ]);
            //     } else {
            //         $stock = CsrStocks::create([
            //             'ris_no' => $r['risno'],
            //             'cl2comb' => $r['cl2comb'],
            //             'uomcode' => $r['uomcode'],
            //             'supplierID' => $r['supplier'] != null ? $r['supplier']['supplierID'] : null,
            //             'chrgcode' => $r['fsid'],
            //             'quantity' => $r['releaseqty'],
            //             // 'manufactured_date' => Carbon::parse($r['manufactured_date'])->format('Y-m-d H:i:s.v'),
            //             'delivered_date' => Carbon::parse($r['delivered_date'])->format('Y-m-d H:i:s.v'),
            //             'expiration_date' => Carbon::parse($r['expiration_date'])->format('Y-m-d H:i:s.v'),
            //             'acquisition_price' => $r['unitprice'],
            //             'converted' => 'n',
            //         ]);

            //         $stockLog = CsrStocksLogs::create([
            //             'stock_id' => $stock->id,
            //             'ris_no' => $r['risno'],
            //             'cl2comb' => $r['cl2comb'],
            //             'uomcode' => $r['uomcode'],
            //             'supplierID' => $r['supplier'] != null ? $r['supplier']['supplierID'] : null,
            //             'chrgcode' => $r['fsid'],
            //             'prev_qty' => 0,
            //             'new_qty' => $r['releaseqty'],
            //             'quantity' => $r['releaseqty'],
            //             // 'manufactured_date' => Carbon::parse($r['manufactured_date'])->format('Y-m-d H:i:s.v'),
            //             'delivered_date' => Carbon::parse($r['delivered_date'])->format('Y-m-d H:i:s.v'),
            //             'expiration_date' => Carbon::parse($r['expiration_date'])->format('Y-m-d H:i:s.v'),
            //             'action' => 'ADDED DELIVERY',
            //             'remarks' => '',
            //             'acquisition_price' => $r['unitprice'],
            //             'entry_by' => $entry_by,
            //             'converted' => 'n',
            //         ]);
            //     }
            // }

            return redirect()->back();
        }
    }


    public function update(CsrStocks $csrstock, Request $request)
    {
        // dd($request);

        $entry_by = Auth::user()->employeeid;

        $request->validate([
            // 'supplierID' => 'required',
            'expiration_date' => 'required',
            'remarks' => 'required'
        ]);

        $prevStockDetails = CsrStocks::where('id', $csrstock->id)->first();

        $updated = $csrstock->update([
            'supplierID' => $request->supplierID,
            'quantity' => $request->quantity,
            // 'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
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
            'acquisition_price' => $prevStockDetails->acquisition_price,
            'chrgcode' => $prevStockDetails->chrgcode,
            'prev_qty' => $prevStockDetails->quantity,
            'new_qty' => $request->quantity,
            // 'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
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
        // dd($request);

        $prevStockDetails = CsrStocks::where('id', $request->stock_id)->first();

        CsrStocks::where('id', $request->stock_id)
            ->delete();

        $entry_by = Auth::user()->employeeid;

        $stockLog = CsrStocksLogs::create([
            'stock_id' => $prevStockDetails->id,
            'ris_no' => $prevStockDetails->ris_no,
            'cl2comb' => $prevStockDetails->cl2comb,
            'uomcode' => $prevStockDetails->uomcode,
            'supplierID' => $prevStockDetails->supplierID,
            'acquisition_price' => $prevStockDetails->acquisition_price,
            'chrgcode' => $prevStockDetails->chrgcode,
            'prev_qty' => $prevStockDetails->quantity,
            'new_qty' => $prevStockDetails->quantity,
            // 'manufactured_date' => Carbon::parse($prevStockDetails->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' => Carbon::parse($prevStockDetails->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' => Carbon::parse($prevStockDetails->expiration_date)->format('Y-m-d H:i:s.v'),
            'action' => 'DELETE DELIVERY',
            'remarks' => '',
            'entry_by' => $entry_by,
        ]);

        return redirect()->back();
    }
}
