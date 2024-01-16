<?php

namespace App\Http\Controllers\Csr\IssueTankItems;

use App\Events\ItemTankIssued;
use App\Events\RequestTankStock;
use App\Http\Controllers\Controller;
use App\Models\RequestTankStocks;
use App\Models\RequestTankStocksDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class IssueTankItemsController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;
        $from = $request->from;
        $to = $request->to;

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        // $items = Item::where('cl2stat', 'A')
        //     ->orderBy('cl2desc', 'ASC')
        //     ->get(['cl2comb', 'cl2desc']);

        $requestedStocks = RequestTankStocks::with([
            'requested_at_details:wardcode,wardname',
            'requested_by_details:employeeid,firstname,middlename,lastname',
            'approved_by_details',
            'request_stocks_details'
        ])
            ->whereHas('requested_at_details', function ($q) use ($searchString) {
                $q->where('wardname', 'LIKE', '%' . $searchString . '%');
            })
            // ->orWhereHas('requested_by_details', function ($q) use ($searchString) {
            //     $q->where('firstname', 'LIKE', '%' . $searchString . '%')
            //         ->orWhere('middlename', 'LIKE', '%' . $searchString . '%')
            //         ->orWhere('lastname', 'LIKE', '%' . $searchString . '%');
            // })
            // ->orWhereHas('request_stocks_details.item_details', function ($q) use ($searchString) {
            //     $q->where('cl2desc', 'LIKE', '%' . $searchString . '%');
            // })
            ->when(
                $request->from,
                function ($query, $value) {
                    $query->whereDate('created_at', '>=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->to,
                function ($query, $value) {
                    $query->whereDate('created_at', '<=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->status,
                function ($query, $value) {
                    $query->where('status', $value);
                }
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // dd($requestedStocks);

        return Inertia::render('Csr/IssueTankItems/Index', [
            // 'items' => $items,
            'requestedStocks' => $requestedStocks,
            'authWardcode' => $authWardcode,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request);

        $requestStocksID = $request->request_stocks_id;

        $requestStocksContainer = $request->requestStockListDetails;

        // // get location of the request
        $location = RequestTankStocks::where('id', $requestStocksID)->first();

        // // dd('bef');

        foreach ($requestStocksContainer as $rsc) {
            // update the approved_qty in the RequestStocksDetails table
            $requestStockDetails = RequestTankStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();
            $requestStockDetails->update([
                'approved_qty' => $rsc['approved_qty'],
                'remarks' => $rsc['remarks']
            ]);

            RequestTankStocks::where('id', $requestStocksID)
                ->update([
                    'status' => 'FILLED',
                    'approved_by' => $request->approved_by,
                ]);
        }

        // // pass this the parameter in the frontends mounted
        event(new ItemTankIssued(
            [
                $location->location,
                'Item/s issued.'
            ]
        ));

        return Redirect::route('issuetankitems.index');
    }

    public function update(RequestTankStocks $requesttankstock, Request $request)
    {
        // dd($request);

        $requestStocksID = $request->request_stocks_id;

        // delete previous request details
        RequestTankStocksDetails::where('id', $requestStocksID)->delete();

        $requestStocksContainer = $request->requestStockListDetails;

        // // get location of the request
        $location = RequestTankStocks::where('id', $requestStocksID)->first();

        // // dd('bef');

        foreach ($requestStocksContainer as $rsc) {
            // update the approved_qty in the RequestStocksDetails table
            $requestStockDetails = RequestTankStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();
            $requestStockDetails->update([
                'approved_qty' => $rsc['approved_qty'],
                'remarks' => $rsc['remarks']
            ]);

            RequestTankStocks::where('id', $requestStocksID)
                ->update([
                    'status' => 'FILLED',
                    'approved_by' => $request->approved_by,
                ]);
        }

        // // pass this the parameter in the frontends mounted
        event(new ItemTankIssued(
            [
                $location->location,
                'Item/s issued.'
            ]
        ));

        return Redirect::route('issuetankitems.index');
    }

    public function acknowledgedrequest(RequestTankStocks $requesttankstock, Request $request)
    {
        // dd($request);

        // get location of the request
        $location = RequestTankStocks::where('id', $request->request_stock_id)->first();

        RequestTankStocks::where('id', $request->request_stock_id)
            ->update(['status' => 'ACKNOWLEDGED']);

        // update status

        // the parameters result will be send into the frontend
        event(new ItemTankIssued(
            [
                $location->location,
                'Item/s issued.'
            ]
        ));

        return Redirect::route('issuetankitems.index');
    }

    // public function destroy(RequestTankStock $requesttankstock, Request $request)
    // {

    //     // return Redirect::route('issueitems.index');
    // }
}
