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
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestStocksController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        // get auth wardcode
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

        // $items = DB::select(
        //     "SELECT item.cl2comb, item.cl2desc, item.uomcode, uom.uomdesc
        //         FROM hclass2 as item
        //         JOIN huom as uom ON uom.uomcode = item.uomcode
        //         WHERE item.cl2desc LIKE '(piece)%';
        //     ",
        // );

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
            ->where('location', '=', $authCode)
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
            ->where('location', '=', $authCode)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        // dd($requestedStocks);

        $currentWardStocks = WardsStocks::with(['item_details:cl2comb,cl2desc', 'request_stocks', 'unit_of_measurement:uomcode,uomdesc'])
            ->where('location', $authCode)
            ->where('quantity', '!=', 0)
            ->whereHas(
                'request_stocks',
                function ($query) {
                    return $query->where('status', 'RECEIVED');
                }
            )
            ->get();
        $currentWardStocks2 = WardsStocks::with(['item_details:cl2comb,cl2desc', 'request_stocks', 'unit_of_measurement:uomcode,uomdesc'])
            ->where('request_stocks_id', null)
            ->where('location', $authCode)
            ->where('quantity', '!=', 0)
            ->get();
        // dd($currentWardStocks);

        $fundSource = FundSource::orderBy('fsName')
            ->get(['id', 'fsid', 'fsName', 'cluster_code']);

        // $typeOfCharge = TypeOfCharge::where('chrgstat', 'A')
        //     ->where('chrgtable', 'NONDR')
        //     ->get(['chrgcode', 'chrgdesc', 'bentypcod', 'chrgtable']);

        return Inertia::render('Wards/RequestStocks/Index', [
            'items' => $items,
            'medicalGas' => $medicalGas,
            'requestedStocks' => $requestedStocks,
            'authWardcode' => $authWardcode[0],
            'currentWardStocks' => $currentWardStocks,
            'currentWardStocks2' => $currentWardStocks2,
            // 'typeOfCharge' => $typeOfCharge,
            'fundSource' => $fundSource,
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
        // dd($x);

        if ($requestStock->status == 'FILLED') {
            // update status
            RequestStocks::where('id', $request->request_stock_id)
                ->update([
                    'status' => $request->status,
                    'received_date' => Carbon::now(),
                ]);

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
