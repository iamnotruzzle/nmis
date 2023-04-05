<?php

namespace App\Http\Controllers\Csr\Inventory\ItemPrice;

use App\Http\Controllers\Controller;
use App\Models\ItemPrices;
use App\Models\UnitOfMeasurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ItemPriceController extends Controller
{
    public function index(Request $request)
    {
    }

    public function store(Request $request)
    {
        // dd($request);

        $request->validate([
            'selling_price' => 'required|numeric',
        ]);

        $itemPrices = ItemPrices::create([
            'cl2comb' => $request->cl2comb,
            'selling_price' => $request->selling_price,
            'entry_by' => $request->entry_by,
        ]);

        return Redirect::route('items.index');
    }

    public function update(ItemPrices $itemprice, Request $request)
    {
        // dd($request);
        $request->validate([
            'selling_price' => 'required|numeric',
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
