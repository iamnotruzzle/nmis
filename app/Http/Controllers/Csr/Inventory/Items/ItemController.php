<?php

namespace App\Http\Controllers\Csr\Inventory\Items;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemReorderLevel;
use App\Models\PimsCategory;
use App\Models\UnitOfMeasurement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $catID = $request->catID; // main category
        $cl1desc = $request->cl1desc; // sub category

        $cl1combs = Category::where('cl1stat', 'A')
            ->where('ptcode', '1000')
            ->orderBy('cl1comb', 'ASC')
            ->get(['cl1comb', 'cl1desc']);

        $units = UnitOfMeasurement::where('uomstat', 'A')
            ->orderBy('uomdesc', 'ASC')
            ->get(['uomcode', 'uomdesc']);

        $pimsCategory = PimsCategory::orderBy('categoryname', 'ASC')->get(['id', 'catID', 'categoryname']);

        $items = DB::select(
            "SELECT item.cl2comb, item.cl2code, main_category.categoryname as main_category,  category.cl1comb as cl1comb,
                category.cl1desc as sub_category, item.catID, item.cl2desc as item,
                price.id as price_id, price.selling_price as price, price.entry_by, employee.firstname as entry_by_firstname, employee.middlename as entry_by_middlename, employee.lastname as entry_by_lastname, price.created_at as price_created_at,
                unit.uomcode, unit.uomdesc as unit,
                reoder_level.normal_stock as normal_stock, reoder_level.alert_stock, reoder_level.critical_stock,
                item.cl2stat
            FROM hclass2 item
            JOIN huom as unit ON item.uomcode = unit.uomcode
            JOIN hclass1 as category ON item.cl1comb = category.cl1comb
            LEFT JOIN csrw_item_prices as price  ON item.cl2comb = price.cl2comb
            JOIN csrw_pims_categories as main_category ON item.catID = main_category.catID
            LEFT JOIN hpersonal as employee ON price.entry_by = employee.employeeid
            LEFT JOIN (
                SELECT TOP 1 r.cl2comb, r.normal_stock as normal_stock, r.alert_stock, r.critical_stock
                FROM csrw_item_reorder_level as r
                WHERE r.location = 'CSR'
                OR r.location = 'ADMIN'
                ORDER BY r.created_at DESC
            ) as reoder_level ON item.cl2comb = reoder_level.cl2comb
            WHERE item.cl2comb LIKE '%1000-%'
            --AND item.cl2desc LIKE '%test test test test test%'
            ORDER BY item.cl2desc ASC;"
        );

        // dd($items);

        return Inertia::render('Csr/Inventory/Items/Index', [
            'cl1combs' => $cl1combs,
            'pimsCategory' => $pimsCategory,
            'items' => $items,
            'units' => $units,
        ]);
    }

    public function generateUniqueID($length = 5)
    {
        return Str::random($length);
    }

    public function store(Request $request)
    {
        // dd($request);

        $uniqueID = $this->generateUniqueID();

        $request->validate([
            'cl1comb' => 'required',
            'cl2desc' => 'required|max:255',
            'unit' => 'required',
            'cl2stat' => 'required|max:1',
        ]);

        $item = Item::create([
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

        $itemReorderLevel = ItemReorderLevel::create([
            'cl2comb' => $item->cl2comb,
            'normal_stock' => $request->normal_stock,
            'alert_stock' => $request->alert_stock,
            'critical_stock' => $request->critical_stock,
            'entry_by' => $request->entry_by,
            'location' => $request->location,
        ]);

        // dd($item);

        return Redirect::route('items.index');
    }

    public function update(Item $item, Request $request)
    {
        // dd($request);

        $request->validate([
            'cl1comb' => 'required|max:20',
            'cl2desc' => 'max:200',
            'unit' => 'required',
            'cl2stat' => 'required|max:1',
        ]);

        $updated_item =  $item->update([
            'catID' => $request->mainCategory, // main category
            'cl2comb' => trim($request->cl1comb) . '-' . trim($request->cl2code),
            'cl1comb' => trim($request->cl1comb), // sub category
            'cl2desc' => trim($request->cl2desc), // item desc
            'uomcode' => $request->unit, // unit
            'cl2stat' => $request->cl2stat,
        ]);

        $itemReorderLevel = ItemReorderLevel::create([
            'cl2comb' => trim($request->cl1comb) . '-' . trim($request->cl2code),
            'normal_stock' => $request->normal_stock,
            'alert_stock' => $request->alert_stock,
            'critical_stock' => $request->critical_stock,
            'entry_by' => $request->entry_by,
            'location' => $request->location,
        ]);

        return Redirect::route('items.index');
    }

    public function destroy(Item $item)
    {
        // $item->delete();

        return Redirect::route('items.index');
    }
}
