<?php

namespace App\Http\Controllers\Csr\Utility\ManualAddStocks;

use App\Http\Controllers\Controller;
use App\Models\CsrStocks;
use App\Models\Item;
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

        $unit = Item::where('cl2comb', $request->cl2comb)->first('uomcode');
        // dd($unit->uomcode);

        $stock = CsrStocks::create([
            'ris_no' => $request->ris_no,
            'cl2comb' => $request->cl2comb,
            'uomcode' => $unit->uomcode,
            'suppcode' => $request->supplier,
            'chrgcode' => $request->fund_source,
            'quantity' => $request->quantity,
            'manufactured_date' => $request->manufactured_date,
            'delivered_date' => $request->delivered_date,
            'expiration_date' => $request->expiration_date,
            'acquisition_price' => $request->acquisitionPrice,
            'mark_up' => $request->markupPercentage,
            'selling_price' => $request->sellingPrice,
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
