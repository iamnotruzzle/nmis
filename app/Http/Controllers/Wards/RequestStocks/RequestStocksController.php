<?php

namespace App\Http\Controllers\Wards\RequestStocks;

use App\Events\RequestStock;
use App\Http\Controllers\Controller;
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

        // OLD
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
        // dd($items);

        $medicalGas = DB::select(
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
                item.catID = 1
                AND item.uomcode != 'box'
                AND item.itemcode LIKE 'MSMG-%'
            ORDER BY
                item.cl2desc ASC;"
        );
        // dd($medicalGas);

        $requestedStocks = RequestStocks::with(['requested_at_details', 'requested_by_details', 'approved_by_details', 'request_stocks_details.item_details'])
            ->where('location', '=', $wardCode)
            ->whereHas('requested_by_details', function ($q) use ($searchString) {
                $q->where('firstname', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('middlename', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('lastname', 'LIKE', '%' . $searchString . '%');
            })
            // ->orWhereHas('request_stocks_details.item_details', function ($q) use ($searchString) {
            //     $q->where('cl2desc', 'LIKE', '%' . $searchString . '%');
            // })
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
            ->paginate(15);
        // dd($requestedStocks);

        // // FROM CSR
        // $currentWardStocks = WardsStocks::with(['item_details:cl2comb,cl2desc', 'request_stocks', 'unit_of_measurement:uomcode,uomdesc'])
        //     ->where('location', $wardCode)
        //     ->where('quantity', '!=', 0)
        //     ->whereHas(
        //         'request_stocks',
        //         function ($query) {
        //             return $query->where('status', 'RECEIVED');
        //         }
        //     )
        //     ->get();
        // // FROM other sources
        // $currentWardStocks2 = WardsStocks::with(['item_details:cl2comb,cl2desc', 'request_stocks', 'unit_of_measurement:uomcode,uomdesc'])
        //     ->where('request_stocks_id', null)
        //     ->where('location', $wardCode)
        //     ->where('quantity', '!=', 0)
        //     ->get();

        $currentWardStocks = DB::select(
            "SELECT ws.*,
                idt.cl2comb, idt.cl2desc,
                uom.uomcode, uom.uomdesc
                FROM csrw_wards_stocks ws
                LEFT JOIN hclass2 idt ON ws.cl2comb = idt.cl2comb
                LEFT JOIN huom uom ON ws.uomcode = uom.uomcode
                INNER JOIN csrw_request_stocks rs ON rs.id = ws.request_stocks_id
                WHERE ws.location = ?
                AND ws.quantity != 0
                AND rs.status = 'RECEIVED'

                UNION ALL

                SELECT ws.*,
                    idt.cl2comb, idt.cl2desc,
                    uom.uomcode, uom.uomdesc
                FROM csrw_wards_stocks ws
                LEFT JOIN hclass2 idt ON ws.cl2comb = idt.cl2comb
                LEFT JOIN huom uom ON ws.uomcode = uom.uomcode
                WHERE ws.request_stocks_id IS NULL
                AND ws.location = ?
                AND ws.quantity != 0",
            [$wardCode, $wardCode] // Duplicate the parameter
        );

        return Inertia::render('Wards/RequestStocks/Index', [
            'items' => $items,
            'medicalGas' => $medicalGas,
            'requestedStocks' => $requestedStocks,
            'currentWardStocks' => $currentWardStocks,
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

            $stocks = WardsStocks::where('request_stocks_id', $request->request_stock_id)
                ->get();
            // dd($stocks);

            foreach ($stocks as $stk) {
                $wardStockLogs = WardsStocksLogs::create([
                    'request_stocks_id' => $stk->request_stocks_id,
                    'request_stocks_detail_id' => $stk->request_stocks_detail_id,
                    'ris_no' => $stk->ris_no,
                    'stock_id' => $stk->stock_id,
                    'wards_stocks_id' => $stk->id,
                    'is_consumable' => $stk->is_consumable,
                    'location' => $stk->location,
                    'cl2comb' => $stk->cl2comb,
                    'uomcode' => $stk->uomcode,
                    'chrgcode' => $stk->chrgcode,
                    'prev_qty' => 0,
                    'new_qty' => $stk->quantity,
                    'average' => $stk->average,
                    'total_usage' => $stk->total_usage,
                    'manufactured_date' => Carbon::parse($stk->manufactured_date)->format('Y-m-d H:i:s.v'),
                    'delivered_date' =>  Carbon::parse($stk->delivered_date)->format('Y-m-d H:i:s.v'),
                    'expiration_date' => Carbon::parse($stk->expiration_date)->format('Y-m-d H:i:s.v'),
                    'action' => 'CREATE',
                    'remarks' => null,
                    'entry_by' => $entry_by,
                ]);
            }

            // $wardStockLogs = WardsStocksLogs::create([
            //     'request_stocks_id' => null,
            //     'request_stocks_detail_id' => null,
            //     'ris_no' => $tempRisNo,
            //     'stock_id' => null,
            //     'wards_stocks_id' => $medicalGases->id,
            //     'is_consumable' => 'y',
            //     'location' => $request->wardcode,
            //     'cl2comb' => $request->cl2comb,
            //     'uomcode' => $request->uomcode,
            //     'chrgcode' => $request->fund_source,
            //     'prev_qty' => 0,
            //     'new_qty' => $request->quantity,
            //     'average' => $request->average,
            //     'total_usage' => (int)$request->quantity * (int)$request->average,
            //     'manufactured_date' => Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v'),
            //     'delivered_date' =>  Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v'),
            //     'expiration_date' =>  Carbon::maxValue(),
            //     'action' => 'CREATE',
            //     'remarks' => null,
            //     'entry_by' => $entry_by,
            // ]);

            // the parameters result will be send into the frontend
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
