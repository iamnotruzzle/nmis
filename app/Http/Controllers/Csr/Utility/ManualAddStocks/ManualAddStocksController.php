<?php

namespace App\Http\Controllers\Csr\Utility\ManualAddStocks;

use App\Http\Controllers\Controller;
use App\Models\CsrStocks;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualAddStocksController extends Controller
{
    public function index()
    {
        //
    }
    public function store(Request $request)
    {
        // dd($request);

        $manufactured_date = Carbon::parse($request->manufactured_date)->format('Y-m-d H:i:s.v');
        $delivered_date = Carbon::parse($request->delivered_date)->format('Y-m-d H:i:s.v');
        $expiration_date = Carbon::parse($request->expiration_date)->format('Y-m-d H:i:s.v');

        $unit = Item::where('cl2comb', $request->cl2comb)->first('uomcode');
        // dd($unit->uomcode);

        $stock = CsrStocks::create([
            'ris_no' => $request->ris_no,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $unit->uomcode,
            'supplierID' => $request->supplier,
            'chrgcode' => $request->fund_source,
            'quantity' => $request->quantity,
            'manufactured_date' => $manufactured_date,
            'delivered_date' => $delivered_date,
            'expiration_date' => $expiration_date,
            'acquisition_price' => $request->acquisitionPrice,
            'converted' => 'n',
        ]);

        return redirect()->back();
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
