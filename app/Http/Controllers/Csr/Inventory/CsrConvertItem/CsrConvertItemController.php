<?php

namespace App\Http\Controllers\Csr\Inventory\CsrConvertItem;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CsrConvertItemController extends Controller
{
    public function index()
    {
        //
    }

    public function generateUniqueID($length = 5)
    {
        return Str::random($length);
    }


    public function store(Request $request)
    {
        // dd($request);

        $uniqueID = $this->generateUniqueID();

        $updated_item =  Item::create([
            'cl2comb' => trim($request->cl1comb) . '-' . $uniqueID,
            'cl1comb' => trim($request->cl1comb),
            'cl2code' => $uniqueID,
            'stkno' => '',
            'cl2desc' => trim($request->cl2desc),
            'cl2retprc' => 0.00,
            'uomcode' => $request->unit,
            'cl2dteas' => Carbon::now(),
            'cl2stat' => $request->cl2stat,
            'cl2lock' => 'N',
            'cl2upsw' => 'P',
            'cl2dtmd' => NULL,
            'curcode' => NULL,
            'cl2purp' => NULL,
            'curcode1' => NULL,
            'uomcode1' => NULL,
            'cl2ctr' => NULL,
            'stockbal' => 0.00,
            'pharmaceutical' => NULL,
            'pharmaceutical' => NULL,
            'baldteasof' => Carbon::now(),
            'begbal' => 0.00,
            'lot_no' => '',
            'barcode' => NULL,
            'rpoint' => NULL,
            'catID' => $request->mainCategory,
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
