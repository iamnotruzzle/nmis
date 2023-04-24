<?php

namespace App\Http\Controllers\Wards\RequestStocks;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\RequestStocks;
use App\Models\RequestStocksDetails;
use App\Models\WardsStocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestStocksController extends Controller
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

        $items = Item::where('cl2stat', 'A')
            ->orderBy('cl2desc', 'ASC')
            ->get(['cl2comb', 'cl2desc']);

        // TODO, requestStocks query has 2 where clause on location.
        // FIX the query where it only needs to use 1 where location instead of 2
        // to get the requested stocks based on the auth's current login locations
        // TODO FIX $requestedStocks where when() is not working when whereHas() is 2 or more
        $requestedStocks = RequestStocks::with(['requested_at_details', 'requested_by_details', 'approved_by_details', 'request_stocks_details.item_details'])
            ->where('location', '=', $authWardcode->wardcode)
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

        $currentWardStocks =
            DB::table('csrw_wards_stocks')
            ->join('hclass2', 'csrw_wards_stocks.cl2comb', '=', 'hclass2.cl2comb')
            ->join('csrw_request_stocks', 'csrw_wards_stocks.request_stocks_id', '=', 'csrw_request_stocks.id')
            ->select('hclass2.cl2desc', DB::raw('SUM(quantity) as quantity'))
            ->whereRaw("csrw_wards_stocks.location = '" . $authWardcode->wardcode . "' AND
                      csrw_request_stocks.status = 'DELIVERED'  ")
            ->groupBy('hclass2.cl2desc')
            ->get();

        return Inertia::render('Wards/Stocks/Index', [
            'items' => $items,
            'requestedStocks' => $requestedStocks,
            'authWardcode' => $authWardcode,
            'currentWardStocks' => $currentWardStocks,
        ]);
    }

    public function store(Request $request)
    {
        $requestStocks = RequestStocks::create([
            'location' => $request->location,
            'status' => 'REQUESTED',
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

        return Redirect::route('requeststocks.index');
    }

    public function destroy(RequestStocks $requeststock, Request $request)
    {
        $requestStocksID = $requeststock->id;

        // delete request stock
        $requeststock->delete();

        // delete request stock details
        RequestStocksDetails::where('request_stocks_id', $requestStocksID)->delete();

        return Redirect::route('requeststocks.index');
    }
}
