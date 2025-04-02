<?php

namespace App\Http\Controllers\Wards\RequestStocks;

use App\Events\RequestStock;
use App\Http\Controllers\Controller;
use App\Jobs\InitializeWardConsumptionTrackerJobs;
use App\Models\FundSource;
use App\Models\Item;
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
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestStocksController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        // Retrieve cached values
        $authWardCode_cached = Cache::get('c_authWardCode_' . Auth::user()->employeeid);
        $wardCode = $authWardCode_cached;

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

        $requestedStocks = RequestStocks::with(['requested_at_details', 'requested_by_details', 'approved_by_details', 'request_stocks_details.item_details'])
            ->where('location', '=', $wardCode)
            ->whereHas('requested_by_details', function ($q) use ($searchString) {
                $q->where('firstname', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('middlename', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('lastname', 'LIKE', '%' . $searchString . '%');
            })
            ->when(
                $request->from,
                function ($query, $value) use ($from) {
                    $query->whereDate('created_at', '>=', $from);
                }
            )
            ->when(
                $request->to,
                function ($query, $value) use ($to) {
                    $query->whereDate('created_at', '<=', $to);
                }
            )
            ->where('location', '=', $wardCode)
            ->orderBy('created_at', 'desc')
            ->paginate(10);


        return Inertia::render('Wards/RequestStocks/Index', [
            'items' => $items,
            'requestedStocks' => $requestedStocks,
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
            // update status
            RequestStocks::where('id', $request->request_stock_id)
                ->update([
                    'status' => $request->status,
                    'received_date' => Carbon::now(),
                ]);

            // OLD
            // $stocks = WardsStocks::where('request_stocks_id', $request->request_stock_id)
            //     ->get();

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

            // comment for now
            // foreach ($stocks as $stk) {
            //     $id = $stk->id;
            //     $item_conversion_id = $stk->stock_id;
            //     $ris_no = $stk->ris_no;
            //     $cl2comb = $stk->cl2comb;
            //     $uomcode = $stk->uomcode;
            //     $location = $stk->location;
            //     $price_id = $stk->price_id;

            //     InitializeWardConsumptionTrackerJobs::dispatch(
            //         $id,
            //         $item_conversion_id,
            //         $ris_no,
            //         $cl2comb,
            //         $uomcode,
            //         $location,
            //         $price_id,
            //     );
            // }

            // // the parameters result will be send into the frontend
            event(new RequestStock('Item requested.'));
        }

        return Redirect::route('requeststocks.index');
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
