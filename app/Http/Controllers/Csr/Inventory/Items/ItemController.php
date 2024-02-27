<?php

namespace App\Http\Controllers\Csr\Inventory\Items;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\PimsCategory;
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
        $catID = $request->catID;

        $cl1combs = Category::where('cl1stat', 'A')
            ->where('ptcode', '1000')
            ->orderBy('cl1comb', 'ASC')
            ->get(['cl1comb', 'cl1desc']);

        $units = UnitOfMeasurement::where('uomstat', 'A')
            ->orderBy('uomdesc', 'ASC')
            ->get(['uomcode', 'uomdesc']);

        // $subCategory = Category::where('catID', null)->get(['cl1code', 'cl1desc']);
        // dd($subCategory);

        $pimsCategory = PimsCategory::orderBy('categoryname', 'DESC')->get(['id', 'catID', 'categoryname']);

        $items = Item::with(['unit', 'prices.userDetail', 'category:cl1comb,cl1desc', 'pims_category:id,catID,categoryname'])
            ->when($catID, function ($query) use ($catID) {
                $query->whereHas('pims_category', function ($q) use ($catID) {
                    $q->where('catID', $catID);
                });
            })
            ->when($request->search, function ($query, $value) {
                $query->where('cl2comb', 'LIKE', '%' . $value . '%')
                    ->orWhere('cl2desc', 'LIKE', '%' . $value . '%');
            })
            ->when(
                $request->status,
                function ($query, $value) {
                    $query->where('cl2stat', $value);
                }
            )
            // ->where('cl2stat', 'A')
            ->whereNotNull('catID')
            ->orderBy('cl2desc', 'ASC')
            ->paginate(10);

        return Inertia::render('Csr/Inventory/Items/Index', [
            'cl1combs' => $cl1combs,
            'pimsCategory' => $pimsCategory,
            'items' => $items,
            'units' => $units,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request);

        $request->validate([
            'cl1comb' => 'required',
            'cl2desc' => 'max:255',
            'unit' => 'required',
            'cl2stat' => 'required|max:1',
        ]);

        // get uomcode of the unit selected
        $unit = UnitOfMeasurement::where('uomstat', 'A')
            ->where('uomdesc', $request->unit)
            ->get(['uomcode']);

        $item = Item::create([
            'cl2comb' => $request->cl1comb . '' . $request->cl2code,
            'cl1comb' => trim($request->cl1comb),
            'cl2code' => '101',
            'stkno' => '',
            'cl2desc' => $request->cl2desc,
            'cl2retprc' => 0.00,
            'uomcode' => $unit[0]->uomcode,
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
            'brandname' => NULL,
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

        // dd($item);

        return Redirect::route('items.index');
    }

    public function update(Item $item, Request $request)
    {
        dd($request);

        $request->validate([
            'cl1comb' => 'required|max:20',
            'cl2desc' => 'max:200',
            'unit' => 'required',
            'cl2stat' => 'required|max:1',
        ]);

        // get uomcode of the unit selected
        $unit = UnitOfMeasurement::where('uomstat', 'A')
            ->where('uomdesc', $request->unit)
            ->get(['uomcode']);

        $item->update([
            'cl2comb' => $request->cl1comb . '' . $request->cl2code,
            'cl1comb' => trim($request->cl1comb),
            'cl2code' => trim($request->cl2code),
            'stkno' => '',
            'cl2desc' => $request->cl2desc,
            'cl2retprc' => 0.00,
            'uomcode' => $unit[0]->uomcode,
            // 'cl2dteas' => Carbon::now(),
            'cl2stat' => $request->cl2stat,
            'cl2lock' => 'N',
            'cl2upsw' => 'P',
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
