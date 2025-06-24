<?php

namespace App\Http\Controllers\Wards\RequestStocks;

use App\Events\RequestStock;
use App\Http\Controllers\Controller;
use App\Models\FundSource;
use App\Models\Item;
use App\Models\LocationStockBalanceDateLogs;
use App\Models\RequestStocks;
use App\Models\RequestStocksDetails;
use App\Models\TypeOfCharge;
use App\Models\WardsStocks;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use App\Models\Sessions;
use App\Models\WardConsumptionTracker;
use App\Models\WardsStocksLogs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestStocksController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

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

        // available items only show if quantity_after == total_issued_qty
        $items = DB::select(
            "SELECT
                item.cl2comb,
                item.cl2desc,
                item.uomcode,
                uom.uomdesc
            FROM
                hclass2 AS item
            FULL OUTER JOIN
                huom AS uom
                ON uom.uomcode = item.uomcode
            WHERE
                (item.catID = 1
                AND item.uomcode != 'box'
                AND (item.itemcode NOT LIKE 'MSMG-%' OR item.itemcode IS NULL))
            ORDER BY
                item.cl2desc ASC;"
        );

        // OPTIMIZE TTHIS
        // if ($authCode == 'ER') {
        //     $requestedStocks = RequestStocks::with(['requested_at_details', 'requested_by_details', 'approved_by_details', 'request_stocks_details.item_details'])
        //         ->where('location', '=', $authCode)
        //         ->whereHas('requested_by_details', function ($q) use ($searchString) {
        //             $q->where('firstname', 'LIKE', '%' . $searchString . '%')
        //                 ->orWhere('middlename', 'LIKE', '%' . $searchString . '%')
        //                 ->orWhere('lastname', 'LIKE', '%' . $searchString . '%');
        //         })
        //         ->when($request->status, function ($query, $value) {
        //             $query->where('status', $value);
        //         })
        //         ->when(
        //             $request->from,
        //             function ($query, $value) use ($from) {
        //                 $query->whereDate('created_at', '>=', $from);
        //             }
        //         )
        //         ->when(
        //             $request->to,
        //             function ($query, $value) use ($to) {
        //                 $query->whereDate('created_at', '<=', $to);
        //             }
        //         )
        //         ->where('location', '=', $authCode)
        //         ->orderBy('created_at', 'desc')
        //         ->paginate(2);
        // } else {
        $requestedStocks = DB::table('csrw_request_stocks as rs')
            ->select([
                'rs.id',
                'rs.location',
                'rs.status',
                'rs.received_date',
                'rs.created_at',

                // Requested from (ward/location)
                'loc.wardname as requested_from',

                // Requested by
                'rb.firstname as requested_by_firstname',
                'rb.lastname as requested_by_lastname',

                // Approved by
                'ab.firstname as approved_by_firstname',
                'ab.lastname as approved_by_lastname',
            ])
            ->leftJoin('hward as loc', 'rs.location', '=', 'loc.wardcode')
            ->leftJoin('hpersonal as rb', 'rs.requested_by', '=', 'rb.employeeid')
            ->leftJoin('hpersonal as ab', 'rs.approved_by', '=', 'ab.employeeid')
            ->where('rs.location', $authCode)
            ->when($searchString, function ($query, $searchString) {
                $query->where(function ($q) use ($searchString) {
                    $q->where('rb.firstname', 'LIKE', "%$searchString%")
                        ->orWhere('rb.lastname', 'LIKE', "%$searchString%");
                });
            })
            ->when($request->status, fn($q, $status) => $q->where('rs.status', $status))
            ->when($request->from, fn($q, $from) => $q->whereDate('rs.created_at', '>=', $from))
            ->when($request->to, fn($q, $to) => $q->whereDate('rs.created_at', '<=', $to))
            ->orderBy('rs.created_at', 'desc')
            ->paginate(3);

        $ids = collect($requestedStocks->items())->pluck('id')->toArray();
        $requestStocksDetails = DB::table('csrw_request_stocks_details as rsd')
            ->select([
                'rsd.request_stocks_id',
                'rsd.id',
                'rsd.cl2comb',
                'rsd.requested_qty',
                'rsd.approved_qty',
                'item.cl2desc',
                'item.uomcode',
                'unit.uomdesc'
            ])
            ->leftJoin('hclass2 as item', 'rsd.cl2comb', '=', 'item.cl2comb')
            ->leftJoin('huom as unit', 'item.uomcode', '=', 'unit.uomcode')
            ->whereIn('rsd.request_stocks_id', $ids)
            ->get()
            ->groupBy('request_stocks_id');

        foreach ($requestedStocks as $stock) {
            $stock->request_stocks_details = $requestStocksDetails[$stock->id] ?? [];
        }
        // }

        // dd($requestedStocks);

        $latestDateLog = LocationStockBalanceDateLogs::where('wardcode', $authCode)
            ->latest('created_at')->first();
        $canTransact = null;
        if ($latestDateLog == null) {
            $canTransact = false;
        } else if ($latestDateLog != null && $latestDateLog->end_bal_created_at != null) {
            $canTransact = false;
        } else {
            $canTransact = true;
        }

        return Inertia::render('Wards/RequestStocks/Index', [
            'items' => $items,
            'requestedStocks' => $requestedStocks,
            'canTransact' => $canTransact,
        ]);
    }

    public function store(Request $request)
    {
        $requestStocks = RequestStocks::create([
            'location' => $request->location,
            'status' => 'PENDING',
            'requested_by' => $request->requested_by,
        ]);
        $requestStocksID = $requestStocks['id'];

        $requestStockListDetails = $request->requestStockListDetails;

        foreach ($requestStockListDetails as $item) {
            RequestStocksDetails::create([
                'request_stocks_id' => $requestStocksID,
                'cl2comb' => $item['cl2comb'],
                'requested_qty' => $item['requested_qty'],
            ]);
        }

        // the parameters result will be send into the frontend
        event(new RequestStock(RequestStocks::where('status', 'PENDING')->count()));

        return Redirect::route('requeststocks.index');
    }

    public function update(RequestStocks $requeststock, Request $request)
    {
        $requestStocksID = $request->request_stocks_id;

        // if the total count of the container is 0,
        // delete both RequestStocks and RequestStocksDetails
        if (count($request->requestStockListDetails) == 0) {
            RequestStocks::where('id', $requestStocksID)->delete();
            RequestStocksDetails::where('request_stocks_id', $requestStocksID)->delete();
        } else {
            RequestStocksDetails::where('request_stocks_id', $requestStocksID)->delete();
            foreach ($request->requestStockListDetails as $item) {
                RequestStocksDetails::create([
                    'request_stocks_id' => $requestStocksID,
                    'cl2comb' => $item['cl2comb'],
                    'requested_qty' => $item['requested_qty'],
                ]);
            }
        }

        // the parameters result will be send into the frontend
        event(new RequestStock('Item requested.'));

        return Redirect::route('requeststocks.index');
    }

    public function updatedeliverystatus(RequestStocks $requeststock, Request $request)
    {
        $requestStock = RequestStocks::where('id', $request->request_stock_id)->first();

        $entry_by = Auth::user()->employeeid;

        if ($requestStock->status == 'FILLED') {
            DB::beginTransaction();

            try {

                // update status
                RequestStocks::where('id', $request->request_stock_id)
                    ->update([
                        'status' => $request->status,
                        'received_date' => Carbon::now(),
                    ]);

                // NEW: includes price
                $stocks = DB::select(
                    "SELECT ward_stock.id, ward_stock.stock_id, ward_stock.request_stocks_id, ward_stock.request_stocks_detail_id, ward_stock.stock_id, ward_stock.location, ward_stock.cl2comb,
                    ward_stock.uomcode, ward_stock.chrgcode, ward_stock.quantity, ward_stock.[from], ward_stock.manufactured_date, ward_stock.delivered_date, ward_stock.expiration_date, ward_stock.created_at,
                    ward_stock.ris_no, price.id as price_id
                    FROM csrw_wards_stocks as ward_stock
                    JOIN csrw_item_prices as price ON price.cl2comb = ward_stock.cl2comb AND price.ris_no = ward_stock.ris_no
                    WHERE ward_stock.request_stocks_id = ?
                    AND ward_stock.quantity > 0;",
                    [$request->request_stock_id]
                );

                foreach ($stocks as $stk) {
                    // dd($stk);
                    $wardStockLogs = WardsStocksLogs::create([
                        'request_stocks_id' => $stk->request_stocks_id,
                        'request_stocks_detail_id' => $stk->request_stocks_detail_id,
                        'ris_no' => $stk->ris_no,
                        'stock_id' => $stk->stock_id,
                        'wards_stocks_id' => $stk->id,
                        'location' => $stk->location,
                        'cl2comb' => $stk->cl2comb,
                        'uomcode' => $stk->uomcode,
                        'chrgcode' => $stk->chrgcode,
                        'prev_qty' => 0,
                        'new_qty' => $stk->quantity,
                        'manufactured_date' => Carbon::parse($stk->manufactured_date)->format('Y-m-d H:i:s.v'),
                        'delivered_date' =>  Carbon::parse($stk->delivered_date)->format('Y-m-d H:i:s.v'),
                        'expiration_date' => Carbon::parse($stk->expiration_date)->format('Y-m-d H:i:s.v'),
                        'action' => 'CREATE',
                        'remarks' => null,
                        'entry_by' => $entry_by,
                    ]);
                }

                foreach ($stocks as $stk) {
                    $id = $stk->id;
                    $item_conversion_id = $stk->stock_id;
                    $ris_no = $stk->ris_no;
                    $cl2comb = $stk->cl2comb;
                    $uomcode = $stk->uomcode;
                    $quantity = $stk->quantity;
                    $location = $stk->location;
                    $price_id = $stk->price_id;
                    $from = $stk->from;

                    // $this->requestStocksForTrackerLog(
                    //     $id,
                    //     $item_conversion_id,
                    //     $ris_no,
                    //     $cl2comb,
                    //     $uomcode,
                    //     $quantity,
                    //     $location,
                    //     $price_id,
                    //     $from,
                    // );

                    // Check if this stock already exists in the tracker with no end balance (meaning it's still in progress)
                    $existingTracker = WardConsumptionTracker::where('ward_stock_id', $id)
                        ->where('cl2comb', $cl2comb)
                        ->where('price_id', $price_id)
                        ->whereNull('end_bal_date')
                        ->exists();

                    if (!$existingTracker) {
                        // New stock has been received after beginning balance, so create a new row
                        WardConsumptionTracker::create([
                            'ward_stock_id'    => $id,
                            'item_conversion_id' => $item_conversion_id,
                            'ris_no'           => $ris_no,
                            'cl2comb'          => $cl2comb,
                            'uomcode'          => $uomcode,
                            'initial_qty'      => $quantity,
                            'beg_bal_date'     => null, // intentionally left null
                            'beg_bal_qty'      => 0, // intentionally left null
                            'location'         => $location,
                            'item_from'        => $from, // Whether it's from CSR or a ward
                            'price_id'         => $price_id,
                        ]);
                    }
                }

                // // the parameters result will be send into the frontend
                event(new RequestStock('Item requested.'));

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();

                Log::error('Stock processing failed', [
                    'request_stock_id' => $request->request_stock_id,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                throw $e; // optional: you can handle/log this as needed
            }
        }


        return Redirect::route('requeststocks.index');
    }

    public function viewItemReOrderQuantity(Request $request)
    {
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

        $result = DB::select(
            "SELECT lvl.cl2comb, item.cl2desc, lvl.reorder_quantity
                FROM csrw_wards_stock_level as lvl
                JOIN hclass2 as ITEM ON item.cl2comb = lvl.cl2comb
                WHERE lvl.wardcode = ?;",
            [$authCode]
        );

        return $result;
    }

    public function requestStocksForTrackerLog(
        $id,
        $item_conversion_id,
        $ris_no,
        $cl2comb,
        $uomcode,
        $quantity,
        $location,
        $price_id,
        $from,
    ) {
        // Check if this stock already exists in the tracker with no end balance (meaning it's still in progress)
        $existingTracker = WardConsumptionTracker::where('ward_stock_id', $id)
            ->where('cl2comb', $cl2comb)
            ->where('price_id', $price_id)
            ->whereNull('end_bal_date')
            ->exists();

        if (!$existingTracker) {
            // New stock has been received after beginning balance, so create a new row
            WardConsumptionTracker::create([
                'ward_stock_id'    => $id,
                'item_conversion_id' => $item_conversion_id,
                'ris_no'           => $ris_no,
                'cl2comb'          => $cl2comb,
                'uomcode'          => $uomcode,
                'initial_qty'      => $quantity,
                'beg_bal_date'     => null, // intentionally left null
                'beg_bal_qty'      => 0, // intentionally left null
                'location'         => $location,
                'item_from'        => $from, // Whether it's from CSR or a ward
                'price_id'         => $price_id,
            ]);
        }
    }

    public function destroy(RequestStocks $requeststock, Request $request)
    {

        // dd($requeststock->id);
        $requestStocksID = $requeststock->id;

        // delete request stock
        // $requeststock->delete();

        // // delete request stock details
        // RequestStocksDetails::where('request_stocks_id', $requestStocksID)->delete();

        RequestStocks::where('id', $requestStocksID)
            ->update([
                'status' => 'CANCELLED',
            ]);

        // the parameters result will be send into the frontend
        event(new RequestStock('Item requested.'));

        return Redirect::route('requeststocks.index');
    }
}
