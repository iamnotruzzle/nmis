<?php

namespace App\Http\Controllers\Wards\TransferStock;

use App\Http\Controllers\Controller;
use App\Jobs\ReceiveItemAfterBegBalJobs;
use App\Jobs\ReceiveItemFromWardJobs;
use App\Jobs\TransferringWardConsumptionTrackerJobs;
use App\Models\ItemPrices;
use App\Models\LocationStockBalance;
use App\Models\UserDetail;
use App\Models\WardTransferStock;
use Illuminate\Http\Request;
use App\Models\WardsStocks;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class TransferStockController extends Controller
{

    public function index(Request $request)
    {
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

        // FROM CSR
        $wardStocks = WardsStocks::with(['item_details:cl2comb,cl2desc', 'request_stocks'])
            ->where('quantity', '>', 0)
            ->where('location', '=', $authCode)
            ->whereHas('request_stocks', function ($query) {
                return $query->where('status', 'RECEIVED');
            })
            ->where('from', 'CSR')
            ->get();

        // FROM TRANSFERRED STOCKS
        $wardStocks2 = WardsStocks::with(['item_details:cl2comb,cl2desc'])
            ->where('quantity', '>', 0)
            ->where('location', '=', $authCode)
            ->where(function ($query) {
                $query->where('from', 'WARD')
                    ->orWhere('from', 'CONSIGNMENT')
                    ->orWhere('from', 'EXISTING_STOCKS');
            })
            ->get();
        // dd($wardStocks);

        // dd($wardStocks3);

        $transferredStock = WardTransferStock::with(
            'ward_stock',
            'ward_from:wardcode,wardname',
            'ward_to:wardcode,wardname',
            'requested_by:employeeid,firstname,lastname',
            'approved_by:employeeid,firstname,lastname'
        )
            ->where('from', '=', $authCode)
            ->orWhere('to', '=', $authCode)
            ->orderBy('created_at', 'DESC')
            ->get();

        $employees = UserDetail::where('empstat', 'A')->orderBy('employeeid', 'ASC')->get(['employeeid', 'empstat', 'firstname', 'lastname']);

        $canTransfer = null;

        return Inertia::render('Wards/TransferStock/Index', [
            'authWardcode' => $authWardcode[0],
            'wardStocks' => $wardStocks,
            'wardStocks2' => $wardStocks2,
            // 'wardStocksMedicalGasess' => $wardStocksMedicalGasess,
            'transferredStock' => $transferredStock,
            'employees' => $employees,
            'canTransfer' => $canTransfer,
        ]);
    }


    public function store(Request $request)
    {
        // dd($request);

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

        $request->validate([
            'quantity' => 'required',
            'to' => 'required',
            'requested_by' => 'required',
            'remarks' => 'required',
        ]);

        $transferredStocks = WardTransferStock::create([
            'ward_stock_id' => $request->ward_stock_id,
            'from' => $authCode,
            'to' => $request->to,
            'requested_by' => $request->requested_by,
            'approved_by' => Auth::user()->employeeid,
            'quantity' => $request->quantity,
            'remarks' => $request->remarks,
            'status' => 'TRANSFERRED',
        ]);

        // get current stock data
        $stockThatBeingTransferred = WardsStocks::where('id', $request->ward_stock_id)->first();

        // update new stock quantity
        $stockThatBeingTransferred->update([
            'quantity' => (int)$stockThatBeingTransferred->quantity - (int)$request->quantity
        ]);

        return Redirect::route('transferstock.index');
    }


    public function update(Request $request, $id)
    {
        //
    }

    public function updatetransferstatus(WardTransferStock $wardtransferstock, Request $request)
    {
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

        $wardTransferID = $request->id;

        // update status
        $transferredStock = WardTransferStock::where('id', $wardTransferID)->first();

        $transferredStock->update([
            'status' => 'RECEIVED',
        ]);

        $ward_stock_id = $transferredStock->ward_stock_id;
        $transferred_qty = $transferredStock->quantity;

        // TransferringWardConsumptionTrackerJobs::dispatch(
        //     $ward_stock_id,
        //     $transferred_qty,
        // );

        #region functions for the other ward to receive the item
        // item about to be transferred
        $item = WardsStocks::where([
            'id' => $transferredStock->ward_stock_id,
        ])->first();
        $itemPrice = ItemPrices::where([
            'cl2comb' => $item->cl2comb,
            'ris_no' => $item->ris_no,
        ])->first();


        $newStock = WardsStocks::create([
            'request_stocks_id' => $item->request_stocks_id,
            'request_stocks_detail_id' => $item->request_stocks_detail_id,
            'stock_id' => $item->stock_id,
            'location' => $authCode,
            'cl2comb' => $item->cl2comb,
            'chrgcode' => $item->chrgcode,
            'quantity' => $transferredStock->quantity,
            'uomcode' => $item->uomcode,
            'from' => 'WARD',
            'manufactured_date' => Carbon::parse($item->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivered_date' => Carbon::parse($item->delivered_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' => Carbon::parse($item->expiration_date)->format('Y-m-d H:i:s.v'),
            'ris_no' => $item->ris_no,
            'is_consumable' => $item->is_consumable,
            'average' => $item->average,
            'total_consumed' => $item->total_consumed,
            'total_usage' => $item->total_usage,
        ]);

        $id = $newStock->id;
        $item_conversion_id = $newStock->stock_id;
        $cl2comb = $item->cl2comb;
        $ris_no = $item->ris_no;
        $uomcode = $item->uomcode;
        $initial_qty = $transferredStock->quantity;
        $from = 'WARD';
        $location = $authCode;
        $price_id = $itemPrice->id;


        // ReceiveItemFromWardJobs::dispatch(
        //     $id,
        //     $item_conversion_id,
        //     $cl2comb,
        //     $ris_no,
        //     $uomcode,
        //     $initial_qty,
        //     $from,
        //     $location,
        //     $price_id,
        // );
        #endregion


        return Redirect::route('transferstock.index');
    }


    public function destroy($id)
    {
        //
    }
}
