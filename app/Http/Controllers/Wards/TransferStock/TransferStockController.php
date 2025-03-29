<?php

namespace App\Http\Controllers\Wards\TransferStock;

use App\Http\Controllers\Controller;
use App\Jobs\TransferringWardConsumptionTrackerJobs;
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

        // check if the latest has a beg bal or ending bal
        $balanceDecChecker = LocationStockBalance::where('location', $authCode)->OrderBy('created_at', 'DESC')->first();
        // dd($balanceDecChecker);
        $canTransfer = null;

        // if true, it can generate beginning balance else it can generate ending balance
        if ($balanceDecChecker !== null) {
            $canTransfer = true;
        } else if ($balanceDecChecker == null) {
            $canTransfer = false;
        } else {
            $canTransfer = false;
        }

        $employees = UserDetail::where('empstat', 'A')->orderBy('employeeid', 'ASC')->get(['employeeid', 'empstat', 'firstname', 'lastname']);

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

        // dd($authWardcode->wardcode);

        // dd($request->id);
        $wardTransferID = $request->id;

        // update status
        $transferredStock = WardTransferStock::where('id', $wardTransferID)->first();

        $transferredStock->update([
            'status' => 'RECEIVED',
        ]);

        // dd($transferredStock);

        $wardStock = WardsStocks::where('id', $transferredStock->ward_stock_id)->first();
        // dd($wardStock);

        $existingWardStock = WardsStocks::where([
            'request_stocks_id' => $wardStock->request_stocks_id,
            'stock_id' => $wardStock->stock_id,
            'cl2comb' => $wardStock->cl2comb,
            'ris_no' => $wardStock->ris_no,
            'location' => $authCode,
        ])->first();
        // dd($existingWardStock);

        if ($existingWardStock == null) {
            WardsStocks::create([
                'request_stocks_id' => $wardStock->request_stocks_id,
                'request_stocks_detail_id' => $wardStock->request_stocks_detail_id,
                'stock_id' => $wardStock->stock_id,
                'location' => $authCode,
                'cl2comb' => $wardStock->cl2comb,
                'chrgcode' => $wardStock->chrgcode,
                'quantity' => $transferredStock->quantity,
                'uomcode' => $wardStock->uomcode,
                'from' => 'WARD',
                'manufactured_date' => Carbon::parse($wardStock->manufactured_date)->format('Y-m-d H:i:s.v'),
                'delivered_date' => Carbon::parse($wardStock->delivered_date)->format('Y-m-d H:i:s.v'),
                'expiration_date' => Carbon::parse($wardStock->expiration_date)->format('Y-m-d H:i:s.v'),
                'ris_no' => $wardStock->ris_no,
                'is_consumable' => $wardStock->is_consumable,
                'average' => $wardStock->average,
                'total_consumed' => $wardStock->total_consumed,
                'total_usage' => $wardStock->total_usage,
            ]);
        } else {
            WardsStocks::where('id', $existingWardStock->id)
                ->update(
                    [
                        'request_stocks_id' => $wardStock->request_stocks_id,
                        'request_stocks_detail_id' => $wardStock->request_stocks_detail_id,
                        'stock_id' => $wardStock->stock_id,
                        'location' => $authCode,
                        'cl2comb' => $wardStock->cl2comb,
                        'chrgcode' => $wardStock->chrgcode,
                        'quantity' => $existingWardStock->quantity + $transferredStock->quantity,
                        'uomcode' => $wardStock->uomcode,
                        'from' => 'WARD',
                        'manufactured_date' => Carbon::parse($wardStock->manufactured_date)->format('Y-m-d H:i:s.v'),
                        'delivered_date' => Carbon::parse($wardStock->delivered_date)->format('Y-m-d H:i:s.v'),
                        'expiration_date' => Carbon::parse($wardStock->expiration_date)->format('Y-m-d H:i:s.v'),
                        'ris_no' => $wardStock->ris_no,
                        'is_consumable' => $wardStock->is_consumable,
                        'average' => $wardStock->average,
                        'total_consumed' => $wardStock->total_consumed,
                        'total_usage' => $wardStock->total_usage,
                    ]
                );
        }

        $ward_stocks_id = $transferredStock->ward_stock_id;
        $transferred_qty = $transferredStock->quantity;
        TransferringWardConsumptionTrackerJobs::dispatch(
            $ward_stocks_id,
            $transferred_qty,
        );

        return Redirect::route('transferstock.index');
    }


    public function destroy($id)
    {
        //
    }
}
