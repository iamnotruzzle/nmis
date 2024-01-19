<?php

namespace App\Http\Controllers\Wards\RequestTankStocks;

use App\Events\RequestTankStock;
use App\Http\Controllers\Controller;
use App\Models\RequestTankStocks;
use App\Models\RequestTankStocksDetails;
use App\Models\WardStocksTanks;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class RequestTankStocksController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $requestedStocks = RequestTankStocks::with(['requested_at_details', 'requested_by_details', 'approved_by_details', 'request_stocks_details'])
            ->where('location', '=', $authWardcode->wardcode)
            ->whereHas('requested_by_details', function ($q) use ($searchString) {
                $q->where('firstname', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('middlename', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('lastname', 'LIKE', '%' . $searchString . '%');
            })
            ->when(
                $request->from,
                function ($query, $value) {
                    $query->whereDate('created_at', '>=', $value);
                }
            )
            ->when(
                $request->to,
                function ($query, $value) {
                    $query->whereDate('created_at', '<=', $value);
                }
            )
            ->where('location', '=', $authWardcode->wardcode)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // $currentWardStocks = WardsStocksMedSupp::with(['item_details:cl2comb,cl2desc', 'brand_details:id,name', 'request_stocks', 'unit_of_measurement:uomcode,uomdesc'])
        //     ->where('location', $authWardcode->wardcode)
        //     ->where('quantity', '!=', 0)
        //     ->whereHas(
        //         'request_stocks',
        //         function ($query) {
        //             return $query->where('status', 'RECEIVED');
        //         }
        //     )
        //     ->get();

        $currentWardStocks = DB::table('csrw_wards_stocks_tanks_supp')
            ->select('itemcode', DB::raw('SUM(quantity) AS quantity'))
            ->where('location', $authWardcode->wardcode)
            ->groupBy('itemcode')
            ->get();
        // dd($currentWardStocks);

        return Inertia::render('Wards/TankStocks/Index', [
            'requestedStocks' => $requestedStocks,
            'authWardcode' => $authWardcode,
            'currentWardStocks' => $currentWardStocks,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request);

        $requestStocks = RequestTankStocks::create([
            'location' => $request->location,
            'status' => 'PENDING',
            'requested_by' => $request->requested_by,
        ]);
        $requestStocksID = $requestStocks['id'];

        $requestStockListDetails = $request->requestStockListDetails;

        foreach ($requestStockListDetails as $item) {
            RequestTankStocksDetails::create([
                'request_stocks_id' => $requestStocksID,
                'itemcode' => $item['itemcode'],
                'requested_qty' => $item['requested_qty'],
            ]);
        }

        // the parameters result will be send into the frontend
        event(new RequestTankStock('Item requested.'));

        return Redirect::route('requesttankstocks.index');
    }

    public function update(RequestTankStocks $requesttankstock, Request $request)
    {
        $requestStocksID = $request->request_stocks_id;

        // if the total count of the container is 0,
        // delete both RequestStocks and RequestStocksDetails
        if (count($request->requestStockListDetails) == 0) {
            RequestTankStocks::where('id', $requestStocksID)->delete();
            RequestTankStocksDetails::where('request_stocks_id', $requestStocksID)->delete();
        } else {
            RequestTankStocksDetails::where('request_stocks_id', $requestStocksID)->delete();
            foreach ($request->requestStockListDetails as $item) {
                RequestTankStocksDetails::create([
                    'request_stocks_id' => $requestStocksID,
                    'itemcode' => $item['itemcode'],
                    'requested_qty' => $item['requested_qty'],
                ]);
            }
        }

        // the parameters result will be send into the frontend
        event(new RequestTankStock('Item requested.'));

        return Redirect::route('requesttankstocks.index');
    }

    public function updatedeliverystatus(RequestTankStocks $requesttankstock, Request $request)
    {
        // dd($request);

        // update status
        $request_tank_stocks = RequestTankStocks::where('id', $request->request_stock_id)->first();
        RequestTankStocks::where('id', $request->request_stock_id)
            ->update([
                'status' => $request->status,
                'received_date' => Carbon::now(),
            ]);
        // dd($request_tank_stocks);

        $request_tank_stocks_details = RequestTankStocksDetails::where('request_stocks_id', $request_tank_stocks->id)->get();

        // dd($request_tank_stocks->id);
        // dd($request_tank_stocks_details);

        foreach ($request_tank_stocks_details as $item) {
            WardStocksTanks::create([
                'request_stocks_id' => $request_tank_stocks->id,
                'request_stocks_detail_id' => $item['id'],
                'itemcode' => $item['itemcode'],
                'uomcode' => $item['uomcode'],
                'quantity' => $item['approved_qty'],
                'location' => $request_tank_stocks->location,
                'from' => 'CSR',
            ]);
        }

        // the parameters result will be send into the frontend
        event(new RequestTankStock('Item requested.'));

        return Redirect::route('requesttankstocks.index');
    }

    public function destroy(RequestTankStocks $requesttankstock, Request $request)
    {
        $requestStocksID = $requesttankstock->id;

        RequestTankStocks::where('id', $requestStocksID)
            ->update([
                'status' => 'CANCELLED',
            ]);

        // the parameters result will be send into the frontend
        event(new RequestTankStock('Item requested.'));

        return Redirect::route('requesttankstocks.index');
    }
}
