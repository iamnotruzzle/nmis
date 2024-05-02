<?php

namespace App\Http\Controllers\Csr\Inventory\ItemPrice;

use App\Http\Controllers\Controller;
use App\Models\ItemPrices;
use App\Models\UnitOfMeasurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ItemPriceController extends Controller
{
    public function index(Request $request)
    {
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'selling_price' => 'required|numeric|min:0',
        ]);

        $itemPrices = ItemPrices::create([
            'cl2comb' => $request->cl2comb,
            'selling_price' => $request->selling_price,
            'entry_by' => $user->employeeid,
        ]);

        return Redirect::route('items.index');
    }

    public function update(ItemPrices $itemprice, Request $request)
    {
        // dd($request);
        $request->validate([
            'selling_price' => 'required|numeric|min:0',
        ]);

        $itemprice->update([
            'selling_price' => $request->selling_price,
        ]);

        return Redirect::route('items.index');
    }

    public function destroy(ItemPrices $itemprice)
    {
        $itemprice->delete();

        return Redirect::route('items.index');
    }
}
