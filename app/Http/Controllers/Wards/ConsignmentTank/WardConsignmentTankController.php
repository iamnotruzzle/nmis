<?php

namespace App\Http\Controllers\Wards\ConsignmentTank;

use App\Http\Controllers\Controller;
use App\Models\WardStocksTanks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class WardConsignmentTankController extends Controller
{
    public function index()
    {
        //
    }


    public function store(Request $request)
    {
        // dd($request);

        $request->validate([
            'itemcode' => 'required',
            'quantity' => 'required',
        ]);

        $consignment = WardStocksTanks::create([
            'location' => $request->authLocation,
            'itemcode' => $request->itemcode,
            'quantity' => $request->quantity,
            'from' => 'CONSIGNMENT',
        ]);

        return Redirect::route('requesttankstocks.index');
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
