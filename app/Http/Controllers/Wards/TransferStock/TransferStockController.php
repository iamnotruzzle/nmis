<?php

namespace App\Http\Controllers\Wards\TransferStock;

use App\Http\Controllers\Controller;
use App\Models\UserDetail;
use App\Models\WardTransferStock;
use Illuminate\Http\Request;
use App\Models\WardsStocksMedSupp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class TransferStockController extends Controller
{

    public function index(Request $request)
    {
        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $wardStocks = WardsStocksMedSupp::with(['item_details:cl2comb,cl2desc', 'brand_details:id,name', 'request_stocks'])
            ->where('quantity', '!=', 0)
            ->where('location', '=', $authWardcode->wardcode)
            ->whereHas('request_stocks', function ($query) {
                return $query->where('status', 'RECEIVED');
            })
            // ->orWhere('request_stocks_id', null)
            ->get();
        // dd($wardStocks);

        $wardStocksConsignments = WardsStocksMedSupp::with(['item_details:cl2comb,cl2desc', 'brand_details:id,name'])
            ->where(
                'quantity',
                '!=',
                0
            )
            ->where('location', '=', $authWardcode->wardcode)
            ->where('request_stocks_id', null)
            ->get();
        // dd($wardStocksConsignments);

        $transferredStock = WardTransferStock::with(
            'ward_stock',
            'ward_from:wardcode,wardname',
            'ward_to:wardcode,wardname',
            'requested_by:employeeid,firstname,lastname',
            'approved_by:employeeid,firstname,lastname'
        )
            ->where('from', '=', $authWardcode->wardcode)
            ->orWhere('to', '=', $authWardcode->wardcode)
            ->orderBy('created_at', 'DESC')
            ->get();

        $employees = UserDetail::where('empstat', 'A')->orderBy('employeeid', 'ASC')->get(['employeeid', 'empstat']);

        return Inertia::render('Wards/TransferStock/Index', [
            'authWardcode' => $authWardcode,
            'wardStocks' => $wardStocks,
            'wardStocksConsignments' => $wardStocksConsignments,
            'transferredStock' => $transferredStock,
            'employees' => $employees,
        ]);
    }


    public function store(Request $request)
    {
        // dd($request);

        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $request->validate([
            'quantity' => 'required',
            'to' => 'required',
            'requested_by' => 'required',
            'remarks' => 'required',
        ]);

        $transferredStocks = WardTransferStock::create([
            'ward_stock_id' => $request->ward_stock_id,
            'from' => $authWardcode->wardcode,
            'to' => $request->to,
            'requested_by' => $request->requested_by,
            'approved_by' => Auth::user()->employeeid,
            'quantity' => $request->quantity,
            'remarks' => $request->remarks,
            'status' => 'TRANSFERRED',
        ]);

        // get current stock data
        $stockThatBeingTransferred = WardsStocksMedSupp::where('id', $request->ward_stock_id)->first();

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
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        // dd($request->id);
        $wardTransferID = $request->id;

        // update status
        $transferredStock = WardTransferStock::where('id', $wardTransferID)->first();

        $transferredStock->update([
            'status' => 'RECEIVED',
        ]);

        // dd($transferredStock);

        $wardStock = WardsStocksMedSupp::where('id', $transferredStock->ward_stock_id)->first();
        // dd($wardStock);

        // create the stock
        WardsStocksMedSupp::create([
            'request_stocks_id' => $wardStock->request_stocks_id,
            'request_stocks_detail_id' => $wardStock->request_stocks_detail_id,
            'stock_id' => $wardStock->stock_id,
            'location' => $authWardcode->wardcode,
            'cl2comb' => $wardStock->cl2comb,
            'brand' => $wardStock->brand,
            'chrgcode' => $wardStock->chrgcode,
            'quantity' => $transferredStock->quantity,
            'uomcode' => $wardStock->uomcode,
            'from' => $wardStock->from,
            'manufactured_date' => $wardStock->manufactured_date,
            'delivered_date' => $wardStock->delivered_date,
            'expiration_date' => $wardStock->expiration_date,
        ]);

        return Redirect::route('transferstock.index');
    }


    public function destroy($id)
    {
        //
    }
}
