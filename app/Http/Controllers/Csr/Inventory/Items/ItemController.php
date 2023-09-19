<?php

namespace App\Http\Controllers\Csr\Inventory\Items;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\UnitOfMeasurement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $cl1combs = Category::where('cl1stat', 'A')
            ->orderBy('cl1comb', 'ASC')
            ->get(['cl1comb', 'cl1desc']);

        $units = UnitOfMeasurement::where('uomstat', 'A')
            ->orderBy('uomdesc', 'ASC')
            ->get(['uomcode', 'uomdesc']);

        $items = Item::with(['unit', 'prices.userDetail'])
            ->when($request->search, function ($query, $value) {
                $query->where('cl2comb', 'LIKE', '%' . $value . '%')
                    ->orWhere('cl2desc', 'LIKE', '%' . $value . '%');
            })
            ->where('cl2stat', 'A')
            ->orderBy('cl2desc', 'ASC')
            ->paginate(15);

        return Inertia::render('Csr/Inventory/Items/Index', [
            'cl1combs' => $cl1combs,
            'items' => $items,
            'units' => $units,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cl1comb' => 'required|max:20',
            'cl2code' => 'required|unique:hclass2,cl2code|max:10',
            'cl2desc' => 'max:200',
            'unit' => 'required',
            'cl2stat' => 'required|max:1',
            'cl2upsw' => 'required|max:1',
        ]);

        // get uomcode of the unit selected
        $unit = UnitOfMeasurement::where('uomstat', 'A')
            ->where('uomdesc', $request->unit)
            ->get(['uomcode']);

        $items = Item::create([
            'cl2comb' => $request->cl1comb . '' . $request->cl2code,
            'cl1comb' => $request->cl1comb,
            'cl2code' => $request->cl2code,
            'stkno' => '',
            'cl2desc' => $request->cl2desc,
            'cl2retprc' => 0.00,
            'uomcode' => $unit[0]->uomcode,
            'cl2dteas' => Carbon::now(),
            'cl2stat' => $request->cl2stat,
            'cl2lock' => 'N',
            'cl2upsw' => $request->cl2upsw,
            'cl2dtmd' => NULL,
            'curcode' => NULL,
            'cl2purp' => NULL,
            'curcode1' => NULL,
            'uomcode1' => NULL,
            'cl2ctr' => NULL,
            'brandname' => NULL,
            'stockbal' => 0.00,
            'pharmaceutical' => NULL,
            'pharmaceutical' => NULL,
            'baldteasof' => Carbon::now(),
            'begbal' => 0.00,
            'lot_no' => '',
            'barcode' => NULL,
            'rpoint' => NULL,
        ]);

        return Redirect::route('items.index');
    }

    public function update(Item $item, Request $request)
    {
        $request->validate([
            'cl1comb' => 'required|max:20',
            'cl2code' => [
                'required',
                'max:10',
                Rule::unique('hclass2')->ignore($request->cl2comb, 'cl2comb') // 'cl2comb' is the column
            ],
            'cl2desc' => 'max:200',
            'unit' => 'required',
            'cl2stat' => 'required|max:1',
            'cl2upsw' => 'required|max:1',
        ]);

        // get uomcode of the unit selected
        $unit = UnitOfMeasurement::where('uomstat', 'A')
            ->where('uomdesc', $request->unit)
            ->get(['uomcode']);

        $item->update([
            'cl2comb' => $request->cl1comb . '' . $request->cl2code,
            'cl1comb' => $request->cl1comb,
            'cl2code' => $request->cl2code,
            'stkno' => '',
            'cl2desc' => $request->cl2desc,
            'cl2retprc' => 0.00,
            'uomcode' => $unit[0]->uomcode,
            // 'cl2dteas' => Carbon::now(),
            'cl2stat' => $request->cl2stat,
            'cl2lock' => 'N',
            'cl2upsw' => $request->cl2upsw,
            'cl2dtmd' => NULL,
            'curcode' => NULL,
            'cl2purp' => NULL,
            'curcode1' => NULL,
            'uomcode1' => NULL,
            'cl2ctr' => NULL,
            'brandname' => NULL,
            'stockbal' => 0.00,
            'pharmaceutical' => NULL,
            'pharmaceutical' => NULL,
            // 'baldteasof' => Carbon::now(),
            'begbal' => 0.00,
            'lot_no' => '',
            'barcode' => NULL,
            'rpoint' => NULL,
        ]);

        return Redirect::route('items.index');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return Redirect::route('items.index');
    }
}
