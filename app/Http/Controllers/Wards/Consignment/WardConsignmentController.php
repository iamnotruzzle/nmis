<?php

namespace App\Http\Controllers\Wards\Consignment;

use App\Http\Controllers\Controller;
use App\Models\WardsStocksMedSupp;
use App\Models\WardsStocksMedSuppLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class WardConsignmentController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        // dd($request);

        $entry_by = Auth::user()->employeeid;

        $request->validate([
            'fund_source' => 'required',
            'brand' => 'required',
            'cl2comb' => 'required',
            'quantity' => 'required',
            'delivered_date' => 'required',
            'expiration_date' => 'required',
        ]);

        $consignment = WardsStocksMedSupp::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'stock_id' => null,
            'location' => $request->authLocation,
            'brand' => $request->brand,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'chrgcode' => $request->fund_source,
            'quantity' => $request->quantity,
            'from' => 'CONSIGNMENT',
            'manufactured_date' => $request->manufactured_date,
            'delivered_date' => $request->delivered_date,
            'expiration_date' => $request->expiration_date,
        ]);

        $wardStockLogs = WardsStocksMedSuppLogs::create([
            'request_stocks_id' => null,
            'request_stocks_detail_id' => null,
            'stock_id' => null,
            'location' => $request->authLocation,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'brand' => $request->brand,
            'chrgcode' => $request->fund_source,
            'prev_qty' => 0,
            'new_qty' => $request->quantity,
            'manufactured_date' => $request->manufactured_date,
            'delivered_date' => $request->delivered_date,
            'expiration_date' => $request->expiration_date,
            'action' => 'create',
            'remarks' => null,
            'entry_by' => $entry_by,
        ]);

        return Redirect::route('requeststocks.index');
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
