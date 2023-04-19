<?php

namespace App\Http\Controllers\Csr\IssueItems;

use App\Http\Controllers\Controller;
use App\Models\CsrStocks;
use App\Models\Item;
use App\Models\RequestStocks;
use App\Models\RequestStocksDetails;
use App\Models\WardsStocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class IssueItemController extends Controller
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

        $requestedStocks = RequestStocks::with([
            'requested_at_details',
            'requested_by_details',
            'approved_by_details',
            'request_stocks_details.item_details',
            'request_stocks_details.stocks'
        ])
            ->whereHas('requested_by_details', function ($q) use ($searchString) {
                $q->where('firstname', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('middlename', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('lastname', 'LIKE', '%' . $searchString . '%');
            })
            ->orWhereHas('requested_at_details', function ($q) use ($searchString) {
                $q->where('wardname', 'LIKE', '%' . $searchString . '%');
            })
            ->orWhereHas('request_stocks_details.item_details', function ($q) use ($searchString) {
                $q->where('cl2desc', 'LIKE', '%' . $searchString . '%');
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
            ->orderBy('created_at', 'desc')
            ->paginate(15);


        return Inertia::render('Csr/IssueItems/Index', [
            'items' => $items,
            'requestedStocks' => $requestedStocks,
            'authWardcode' => $authWardcode,
        ]);
    }

    public function store(Request $request)
    {
        // dd('store');

        $requestStocksID = $request->request_stocks_id;

        $requestStocksContainer = $request->requestStockListDetails;

        // get location of the request
        $location = RequestStocks::where('id', $requestStocksID)->first();

        foreach ($requestStocksContainer as $rsc) {
            // update the approved_qty in the RequestStocksDetails table
            $requestStockDetails = RequestStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();
            $requestStockDetails->update([
                'approved_qty' => $rsc['approved_qty']
            ]);

            // check current stock of the item
            $current_stock = CsrStocks::where('cl2comb', $rsc['cl2comb'])
                ->sum('quantity');

            // check the current value of issued_qty after the loop
            $remaining_qty_to_be_issued = $rsc['approved_qty'];
            $newStockQty = 0;

            // check if remaining_qty_to_be_issued still has a value > than 0
            while ($remaining_qty_to_be_issued > 0) {

                // get the the specific item that is first to expire and quantity != 0
                $stock = CsrStocks::where('cl2comb', $rsc['cl2comb'])
                    ->where('quantity', '!=', 0)
                    ->orderBy('expiration_date')
                    ->first();

                // execute if block when condition is met then do the while loop again
                if ($stock->quantity >= $remaining_qty_to_be_issued) {
                    $row = CsrStocks::where('id', $stock->id)->first();
                    $row_to_change_status = RequestStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();

                    $issueditem = WardsStocks::create([
                        'request_stocks_id' => $row_to_change_status->request_stocks_id,
                        'request_stocks_detail_id' => $row_to_change_status->id,
                        'stock_id' => $row->id,
                        'location' => $location->location,
                        'cl2comb' => $row_to_change_status->cl2comb,
                        'batch_no' => $row->batch_no,
                        'quantity' => $remaining_qty_to_be_issued,
                        'manufactured_date' => $row->manufactured_date,
                        'delivered_date' => $row->delivered_date,
                        'expiration_date' => $row->expiration_date,
                    ]);

                    $newStockQty = $row->quantity - $remaining_qty_to_be_issued;
                    $remaining_qty_to_be_issued = 0;

                    $row::where('id', $stock->id)
                        ->update([
                            'quantity' => $newStockQty,
                        ]);

                    RequestStocks::where('id', $requestStocksID)
                        ->update([
                            'status' => 'FILLED',
                            'approved_by' => $request->approved_by,
                        ]);
                } else {
                    $remaining_qty_to_be_issued = $remaining_qty_to_be_issued - $stock->quantity;

                    $row = CsrStocks::where('id', $stock->id)->first();
                    $row_to_change_status = RequestStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();

                    $issueditem = WardsStocks::create([
                        'request_stocks_id' => $row_to_change_status->request_stocks_id,
                        'request_stocks_detail_id' => $row_to_change_status->id,
                        'stock_id' => $row->id,
                        'location' => $location->location,
                        'cl2comb' => $row_to_change_status->cl2comb,
                        'batch_no' => $row->batch_no,
                        'quantity' => $row->quantity,
                        'manufactured_date' => $row->manufactured_date,
                        'delivered_date' => $row->delivered_date,
                        'expiration_date' => $row->expiration_date,
                    ]);

                    $row::where('id', $stock->id)
                        ->update([
                            'quantity' => 0,
                        ]);

                    RequestStocks::where('id', $requestStocksID)
                        ->update([
                            'status' => 'FILLED',
                            'approved_by' => $request->approved_by,
                        ]);
                }
            }
        }

        return Redirect::route('issueitems.index');
    }

    public function update(RequestStocks $requeststock, Request $request)
    {
        $requestStocksID = $request->request_stocks_id;
        $requestStocksContainer = $request->requestStockListDetails;

        // update all the requested stocks approved qty to null
        $requestStocksDetails = RequestStocksDetails::where('request_stocks_id', $requestStocksID)
            ->where('request_stocks_id', $requestStocksID)
            ->update([
                'approved_qty' => null,
            ]);

        // get the wards stocks where requestStocksId is true
        $wardStocks = WardsStocks::where('request_stocks_id', $requestStocksID)->get();

        // loop throught the wards stocks
        foreach ($wardStocks as $ws) {
            // get the stock where the stock id is true
            $stock = CsrStocks::where('id', $ws['stock_id'])->first();

            // update the stocks quantity to it's original quantity
            $stock->update([
                'quantity' => $stock->quantity + $ws['quantity'],
            ]);

            // delete the wards stock
            $ws->delete();
        }

        // ****** DO THE LOOP THAT WILL ISSUE THE ITEMS ****** //
        // get location of the request
        $location = RequestStocks::where('id', $requestStocksID)->first();

        // a block to check if the remaining stocks is enough
        // for the requested stock
        foreach ($requestStocksContainer as $rsc) {

            // update the approved_qty in the RequestStocksDetails table
            $requestStockDetails = RequestStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();
            $requestStockDetails->update([
                'approved_qty' => $rsc['approved_qty']
            ]);

            // check current stock of the item
            $current_stock = CsrStocks::where('cl2comb', $rsc['cl2comb'])
                ->sum('quantity');

            // check the current value of issue_qty after the loop
            $remaining_qty_to_be_issued = $rsc['approved_qty'];
            if ($current_stock < $remaining_qty_to_be_issued) {
                return redirect()->back()->with('message', true);
            }
        }

        foreach ($requestStocksContainer as $rsc) {
            // check current stock of the item
            $current_stock = CsrStocks::where('cl2comb', $rsc['cl2comb'])
                ->sum('quantity');

            // check the current value of issued_qty after the loop
            $remaining_qty_to_be_issued = $rsc['approved_qty'];
            $newStockQty = 0;

            // check if remaining_qty_to_be_issued still has a value > than 0
            while ($remaining_qty_to_be_issued > 0) {

                // get the the specific item that is first to expire and quantity != 0
                $stock = CsrStocks::where('cl2comb', $rsc['cl2comb'])
                    ->where('quantity', '!=', 0)
                    ->orderBy('expiration_date')
                    ->first();

                // execute if block when condition is met then do the while loop again
                if ($stock->quantity >= $remaining_qty_to_be_issued) {
                    $row = CsrStocks::where('id', $stock->id)->first();
                    $row_to_change_status = RequestStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();

                    $issueditem = WardsStocks::create([
                        'request_stocks_id' => $row_to_change_status->request_stocks_id,
                        'request_stocks_detail_id' => $row_to_change_status->id,
                        'stock_id' => $row->id,
                        'location' => $location->location,
                        'cl2comb' => $row_to_change_status->cl2comb,
                        'batch_no' => $row->batch_no,
                        'quantity' => $remaining_qty_to_be_issued,
                        'manufactured_date' => $row->manufactured_date,
                        'delivered_date' => $row->delivered_date,
                        'expiration_date' => $row->expiration_date,
                    ]);

                    $newStockQty = $row->quantity - $remaining_qty_to_be_issued;
                    $remaining_qty_to_be_issued = 0;

                    $row::where('id', $stock->id)
                        ->update([
                            'quantity' => $newStockQty,
                        ]);

                    RequestStocks::where('id', $requestStocksID)
                        ->update([
                            'status' => 'FILLED',
                            'approved_by' => $request->approved_by,
                        ]);
                } else {
                    $remaining_qty_to_be_issued = $remaining_qty_to_be_issued - $stock->quantity;

                    $row = CsrStocks::where('id', $stock->id)->first();
                    $row_to_change_status = RequestStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();

                    $issueditem = WardsStocks::create([
                        'request_stocks_id' => $row_to_change_status->request_stocks_id,
                        'request_stocks_detail_id' => $row_to_change_status->id,
                        'stock_id' => $row->id,
                        'location' => $location->location,
                        'cl2comb' => $row_to_change_status->cl2comb,
                        'batch_no' => $row->batch_no,
                        'quantity' => $row->quantity,
                        'manufactured_date' => $row->manufactured_date,
                        'delivered_date' => $row->delivered_date,
                        'expiration_date' => $row->expiration_date,
                    ]);

                    $row::where('id', $stock->id)
                        ->update([
                            'quantity' => 0,
                        ]);

                    RequestStocks::where('id', $requestStocksID)
                        ->update([
                            'status' => 'FILLED',
                            'approved_by' => $request->approved_by,
                        ]);
                }
            }
        }

        return Redirect::route('issueitems.index');
    }

    public function destroy(RequestStocks $requeststock, Request $request)
    {
        return Redirect::route('issueitems.index');
    }
}
