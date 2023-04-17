<?php

namespace App\Http\Controllers\Wards\RequestStocks;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\RequestStocks;
use App\Models\RequestStocksDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class RequestStocksController extends Controller
{
    public function index(Request $request)
    {
        // $searchString = $request->search;

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $items = Item::where('cl2stat', 'A')
            ->orderBy('cl2desc', 'ASC')
            ->get(['cl2comb', 'cl2desc']);

        $requestedStocks = RequestStocks::with(['requested_at_details', 'requested_by_details', 'approved_by_details', 'request_stocks_details.item_details'])
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        // dd($items);

        return Inertia::render('Wards/Stocks/Index', [
            'items' => $items,
            'requestedStocks' => $requestedStocks,
            'authWardcode' => $authWardcode,
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

        return Redirect::route('csrstocks.index');
    }

    public function update(RequestStocks $requeststock, Request $request)
    {
        // $request->validate([
        //     'batch_no' => 'required',
        //     'cl2comb' => 'required',
        //     'quantity' => 'required|numeric',
        //     'expiration_date' => 'required',
        // ]);

        // $csrstock->update([
        //     'batch_no' => $request->batch_no,
        //     'cl2comb' => $request->cl2comb,
        //     'quantity' => $request->quantity,
        //     'manufactured_date' => $request->manufactured_date == null ? null : Carbon::parse($request->manufactured_date)->setTimezone('Asia/Manila'),
        //     'delivered_date' => $request->delivered_date == null ? null : Carbon::parse($request->delivered_date)->setTimezone('Asia/Manila'),
        //     'expiration_date' => $request->expiration_date == null ? null : Carbon::parse($request->expiration_date)->setTimezone('Asia/Manila'),
        // ]);

        return Redirect::route('csrstocks.index');
    }

    public function destroy(RequestStocks $requeststock)
    {
        // $csrstock->delete();

        return Redirect::route('csrstocks.index');
    }
}
