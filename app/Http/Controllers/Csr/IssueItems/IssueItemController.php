<?php

namespace App\Http\Controllers\Csr\IssueItems;

use App\Events\ItemIssued;
use App\Http\Controllers\Controller;
use App\Models\CsrItemConversion;
use App\Models\CsrStocks;
use App\Models\FundSource;
use App\Models\Item;
use App\Models\RequestStocks;
use App\Models\RequestStocksDetails;
use App\Models\WardsStocks;
// use App\Rules\CsrStockBalanceNotDeclaredYetRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class IssueItemController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;
        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        // dd(Carbon::today());

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

        $items = DB::select(
            "SELECT converted.cl2comb_after as cl2comb, item.cl2desc, item.uomcode
                FROM csrw_csr_item_conversion as converted
                JOIN hclass2 as item ON item.cl2comb = converted.cl2comb_after
                WHERE converted.quantity_after != converted.total_issued_qty;"
        );
        // dd($items);

        $requestedStocks = RequestStocks::with([
            'requested_at_details:wardcode,wardname',
            'requested_by_details:employeeid,firstname,middlename,lastname',
            'approved_by_details',
            'request_stocks_details.item_details:cl2comb,cl2desc',
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
            ->when(
                $request->status,
                function ($query, $value) {
                    $query->where('status', $value);
                }
            )
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        // dd($requestedStocks);

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

        $fundSource = FundSource::orderBy('fsName')
            ->get(['id', 'fsid', 'fsName', 'cluster_code']);

        $wardsMedicalGasStock = DB::select(
            "SELECT stock.id as stock_id, ward.wardname, stock.cl2comb, item.cl2desc, stock.quantity, stock.average, stock.total_usage
                FROM csrw_wards_stocks as stock
                JOIN hward as ward ON ward.wardcode = stock.location
                JOIN hclass2 as item ON item.cl2comb = stock.cl2comb
                WHERE stock.[from] = 'MEDICAL GASES'
                AND stock.quantity > 0"
        );

        return Inertia::render('Csr/IssueItems/Index', [
            'items' => $items,
            'requestedStocks' => $requestedStocks,
            'medicalGas' => $medicalGas,
            'fundSource' => $fundSource,
            'wardsMedicalGasStock' => $wardsMedicalGasStock,
            'authWardcode' => $authWardcode[0],
        ]);
    }

    public function store(Request $request)
    {
        // dd($request);

        $requestStocksID = $request->request_stocks_id;

        $requestStocksContainer = $request->requestStockListDetails;

        // get location of the request
        $location = RequestStocks::where('id', $requestStocksID)->first();

        if ($location->status == 'ACKNOWLEDGED') {
            $data = $request;

            foreach ($requestStocksContainer as $rsc) {
                // update the approved_qty in the RequestStocksDetails table
                $requestStockDetails = RequestStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();
                $requestStockDetails->update([
                    'approved_qty' => $rsc['approved_qty'],
                    'remarks' => $rsc['remarks']
                ]);

                // check current stock of the item
                $current_stock = CsrItemConversion::where('cl2comb_after', $rsc['cl2comb'])
                    ->whereDate('expiration_date', '>', Carbon::today())
                    ->sum('quantity_after');

                // check the current value of issued_qty after the loop
                $remaining_qty_to_be_issued = $rsc['approved_qty'];
                $newStockQty = 0;

                // check if remaining_qty_to_be_issued still has a value > than 0
                while ($remaining_qty_to_be_issued > 0) {

                    // get the the specific item that is first to expire and quantity != 0
                    $stock = CsrItemConversion::where('cl2comb_after', $rsc['cl2comb'])
                        ->where('quantity_after', '!=', 0)
                        ->whereDate('expiration_date', '>', Carbon::today())
                        ->orderBy('expiration_date')
                        ->first();

                    // execute if block when condition is met then do the while loop again
                    if ($stock->quantity_after >= $remaining_qty_to_be_issued) {
                        $row = CsrItemConversion::where('id', $stock->id)->first();
                        $row_to_change_status = RequestStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();

                        // $row::where('id', $stock->id)
                        //     ->update([
                        //         'total_issued_qty' => (int)$row->total_issued_qty + (int)$remaining_qty_to_be_issued,
                        //     ]);

                        $item = Item::where('cl2comb', $row->cl2comb_after)->first();

                        $issueditem = WardsStocks::create([
                            'request_stocks_id' => $row_to_change_status->request_stocks_id,
                            'request_stocks_detail_id' => $row_to_change_status->id,
                            'stock_id' => $row->id,
                            'ris_no' => $row->ris_no,
                            'location' => $location->location,
                            'chrgcode' => $row->chrgcode,
                            'cl2comb' => $row_to_change_status->cl2comb,
                            'uomcode' => $item->uomcode,
                            'ris_no' => $row->ris_no,
                            'quantity' => $remaining_qty_to_be_issued,
                            'from' => 'CSR',
                            'manufactured_date' => $row->manufactured_date,
                            'delivered_date' => $row->delivered_date,
                            'expiration_date' => $row->expiration_date,
                        ]);

                        $newStockQty = $row->quantity_after - $remaining_qty_to_be_issued;
                        $issuedQty = $remaining_qty_to_be_issued;
                        $remaining_qty_to_be_issued = 0;


                        $row::where('id', $stock->id)
                            ->update([
                                'quantity_after' => $newStockQty,
                                'total_issued_qty' => (int)$row->total_issued_qty + (int)$issuedQty,
                            ]);

                        RequestStocks::where('id', $requestStocksID)
                            ->update([
                                'status' => 'FILLED',
                                'approved_by' => $request->approved_by,
                            ]);
                    } else {
                        $remaining_qty_to_be_issued = $remaining_qty_to_be_issued - $stock->quantity_after;
                        // dd($remaining_qty_to_be_issued);

                        $row = CsrItemConversion::where('id', $stock->id)->first();
                        $row_to_change_status = RequestStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();

                        $item = Item::where('cl2comb', $row->cl2comb_after)->first();

                        $issueditem = WardsStocks::create([
                            'request_stocks_id' => $row_to_change_status->request_stocks_id,
                            'request_stocks_detail_id' => $row_to_change_status->id,
                            'stock_id' => $row->id,
                            'ris_no' => $row->ris_no,
                            'location' => $location->location,
                            'chrgcode' => $row->chrgcode,
                            'cl2comb' => $row_to_change_status->cl2comb,
                            'uomcode' => $item->uomcode,
                            'ris_no' => $row->ris_no,
                            'quantity' => $row->quantity_after,
                            'from' => 'CSR',
                            'manufactured_date' => $row->manufactured_date,
                            'delivered_date' => $row->delivered_date,
                            'expiration_date' => $row->expiration_date,
                        ]);

                        $row::where('id', $stock->id)
                            ->update([
                                'quantity_after' => 0,
                                'total_issued_qty' => $stock->quantity_after
                            ]);

                        RequestStocks::where('id', $requestStocksID)
                            ->update([
                                'status' => 'FILLED',
                                'approved_by' => $request->approved_by,
                            ]);
                    }
                }
            }

            // pass this the parameter in the frontends mounted
            event(new ItemIssued(
                [
                    $location->location,
                    'Item/s issued.'
                ]
            ));
        }

        return Redirect::route('issueitems.index');
    }

    public function update(RequestStocks $requeststock, Request $request)
    {
        // dd($request);
        $requestStocksID = $request->request_stocks_id;
        // dd($requestStocksID);
        $requestStocksContainer = $request->requestStockListDetails;

        // update all the requested stocks approved qty to null
        $requestStocksDetails = RequestStocksDetails::where('request_stocks_id', $requestStocksID)
            // ->where('request_stocks_id', $requestStocksID)
            ->update([
                'approved_qty' => null,
            ]);

        // get the wards stocks where requestStocksId is true
        $wardStocks = WardsStocks::where('request_stocks_id', $requestStocksID)->get();

        // loop through the wards stocks
        foreach ($wardStocks as $ws) {
            // get the stock where the stock id is true
            $stock = CsrItemConversion::where('id', $ws['stock_id'])->first();

            // update the stocks quantity to it's original quantity
            $stock->update([
                'quantity_after' => $stock->quantity_after + $ws['quantity'],
                'total_issued_qty' => max(0, (int)$stock->total_issued_qty - (int)$ws['quantity']),
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
                'approved_qty' => $rsc['approved_qty'],
                'remarks' => $rsc['remarks']
            ]);

            // check current stock of the item
            $current_stock = CsrItemConversion::where('cl2comb_after', $rsc['cl2comb'])
                ->sum('quantity_after');

            // check the current value of issue_qty after the loop
            $remaining_qty_to_be_issued = $rsc['approved_qty'];
            if ($current_stock < $remaining_qty_to_be_issued) {
                return redirect()->back()->with('message', true);
            }
        }

        // dd('s');

        foreach ($requestStocksContainer as $rsc) {
            // update the approved_qty in the RequestStocksDetails table
            $requestStockDetails = RequestStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();
            $requestStockDetails->update([
                'approved_qty' => $rsc['approved_qty'],
                'remarks' => $rsc['remarks']
            ]);

            // check current stock of the item
            $current_stock = CsrItemConversion::where('cl2comb_after', $rsc['cl2comb'])
                ->whereDate('expiration_date', '>', Carbon::today())
                ->sum('quantity_after');

            // check the current value of issued_qty after the loop
            $remaining_qty_to_be_issued = $rsc['approved_qty'];
            $newStockQty = 0;

            // check if remaining_qty_to_be_issued still has a value > than 0
            while ($remaining_qty_to_be_issued > 0) {

                // get the the specific item that is first to expire and quantity != 0
                $stock = CsrItemConversion::where('cl2comb_after', $rsc['cl2comb'])
                    ->where('quantity_after', '!=', 0)
                    ->whereDate('expiration_date', '>', Carbon::today())
                    ->orderBy('expiration_date')
                    ->first();

                // execute if block when condition is met then do the while loop again
                if ($stock->quantity_after >= $remaining_qty_to_be_issued) {
                    $row = CsrItemConversion::where('id', $stock->id)->first();
                    $row_to_change_status = RequestStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();

                    $item = Item::where('cl2comb', $row->cl2comb_after)->first();

                    $issueditem = WardsStocks::create([
                        'request_stocks_id' => $row_to_change_status->request_stocks_id,
                        'request_stocks_detail_id' => $row_to_change_status->id,
                        'stock_id' => $row->id,
                        'location' => $location->location,
                        'chrgcode' => $row->chrgcode,
                        'cl2comb' => $row_to_change_status->cl2comb,
                        'uomcode' => $item->uomcode,
                        'ris_no' => $row->ris_no,
                        'quantity' => $remaining_qty_to_be_issued,
                        'from' => 'CSR',
                        // 'manufactured_date' => $row->manufactured_date,
                        // 'delivered_date' => $row->delivered_date,
                        // 'expiration_date' => $row->expiration_date,
                        'manufactured_date' => Carbon::parse($row->manufactured_date)->format('Y-m-d H:i:s.v'),
                        'delivered_date' =>  Carbon::parse($row->delivered_date)->format('Y-m-d H:i:s.v'),
                        'expiration_date' =>  Carbon::parse($row->expiration_date)->format('Y-m-d H:i:s.v'),
                    ]);

                    $newStockQty = $row->quantity_after - $remaining_qty_to_be_issued;
                    $issuedQty = $remaining_qty_to_be_issued;
                    $remaining_qty_to_be_issued = 0;

                    $row::where('id', $stock->id)
                        ->update([
                            'quantity_after' => $newStockQty,
                            'total_issued_qty' => (int)$row->total_issued_qty + (int)$issuedQty,
                        ]);

                    RequestStocks::where('id', $requestStocksID)
                        ->update([
                            'status' => 'FILLED',
                            'approved_by' => $request->approved_by,
                        ]);
                } else {
                    $remaining_qty_to_be_issued = $remaining_qty_to_be_issued - $stock->quantity_after;

                    $row = CsrItemConversion::where('id', $stock->id)->first();
                    $row_to_change_status = RequestStocksDetails::where('id', $rsc['request_stocks_details_id'])->first();

                    $item = Item::where('cl2comb', $row->cl2comb_after)->first();

                    $issueditem = WardsStocks::create([
                        'request_stocks_id' => $row_to_change_status->request_stocks_id,
                        'request_stocks_detail_id' => $row_to_change_status->id,
                        'stock_id' => $row->id,
                        'location' => $location->location,
                        'chrgcode' => $row->chrgcode,
                        'cl2comb' => $row_to_change_status->cl2comb,
                        'uomcode' => $item->uomcode,
                        'ris_no' => $row->ris_no,
                        'quantity' => $row->quantity_after,
                        'from' => 'CSR',
                        'manufactured_date' => Carbon::parse($row->manufactured_date)->format('Y-m-d H:i:s.v'),
                        'delivered_date' =>  Carbon::parse($row->delivered_date)->format('Y-m-d H:i:s.v'),
                        'expiration_date' =>  Carbon::parse($row->expiration_date)->format('Y-m-d H:i:s.v'),
                    ]);

                    $row::where('id', $stock->id)
                        ->update([
                            'quantity_after' => 0,
                            'total_issued_qty' => $stock->quantity_after
                        ]);

                    RequestStocks::where('id', $requestStocksID)
                        ->update([
                            'status' => 'FILLED',
                            'approved_by' => $request->approved_by,
                        ]);
                }
            }
        }

        // get the issued item
        $rsd = WardsStocks::where('request_stocks_id', $requestStocksID)->get();
        $rsd_container = [];
        foreach ($rsd as $value) {
            array_push($rsd_container, $value['quantity']);
        }
        // get an array of all items that are non-zero
        $tmp = array_filter($rsd_container);

        // check if the filtered array is all zero else change the request stock status to FILLED
        if (empty($tmp)) {
            RequestStocks::where('id', $requestStocksID)
                ->update([
                    'status' => 'PENDING',
                    'approved_by' => $request->approved_by,
                ]);
        } else {
            RequestStocks::where('id', $requestStocksID)
                ->update([
                    'status' => 'FILLED',
                    'approved_by' => $request->approved_by,
                ]);
        }

        // pass this the parameter in the frontends mounted
        event(new ItemIssued(
            [
                $location->location,
                'Item/s issued.'
            ]
        ));

        return Redirect::route('issueitems.index');
    }

    public function acknowledgedRequest(RequestStocks $requeststock, Request $request)
    {
        // get location of the request
        $location = RequestStocks::where('id', $request->request_stock_id)->first();

        // update status
        RequestStocks::where('id', $request->request_stock_id)
            ->update([
                'status' => 'ACKNOWLEDGED',
            ]);

        // pass this the parameter in the frontends mounted
        event(new ItemIssued(
            [
                $location->location,
                'Request acknowledged.'
            ]
        ));

        return Redirect::route('issueitems.index');
    }

    public function destroy(RequestStocks $requeststock, Request $request)
    {

        return Redirect::route('issueitems.index');
    }
}
