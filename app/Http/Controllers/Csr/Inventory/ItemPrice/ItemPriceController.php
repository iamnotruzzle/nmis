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
            'price_per_unit' => 'required|numeric|min:0',
        ]);

        $itemPrices = ItemPrices::create([
            'cl2comb' => $request->cl2comb,
            'price_per_unit' => $request->price_per_unit,
            'entry_by' => $user->employeeid,
        ]);

        return Redirect::route('items.index');
    }

    public function update(ItemPrices $itemprice, Request $request)
    {
        // dd($request);
        $request->validate([
            'price_per_unit' => 'required|numeric|min:0',
        ]);

        $itemprice->update([
            'price_per_unit' => $request->price_per_unit,
        ]);

        return Redirect::route('items.index');
    }

    public function destroy(ItemPrices $itemprice)
    {
        $itemprice->delete();

        return Redirect::route('items.index');
    }
}
