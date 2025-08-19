<?php

namespace App\Http\Controllers\Tools\Packages;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Miscellaneous;
use App\Models\Package;
use App\Models\PackageDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PackageController extends Controller
{
    public function index()
    {
        return Inertia::render('Tools/Packages/Index', []);
    }

    public function getCombinedItems()
    {
        // Get items
        $items = collect(DB::select(
            "SELECT item.cl2comb, item.cl2desc
                FROM hclass2 AS item
                JOIN huom AS uom ON uom.uomcode = item.uomcode
                WHERE (item.catID = 1
                AND (item.itemcode NOT LIKE 'MSMG-%' OR item.itemcode IS NULL))"
        ))->map(function ($item) {
            return [
                'id' => $item->cl2comb,
                'item_description' => $item->cl2desc
            ];
        });

        // Get miscellaneous
        $misc = Miscellaneous::where('hmstat', 'A')
            ->get(['hmcode', 'hmdesc'])
            ->map(function ($miscItem) {
                return [
                    'id' => $miscItem->hmcode,
                    'item_description' => $miscItem->hmdesc
                ];
            });

        // Combine and sort by description in ascending order
        $combined = $items->concat($misc)
            ->sortBy('item_description', SORT_REGULAR, false) // false = ascending
            ->values(); // Reset array keys

        return response()->json($combined);
    }
    public function getPackages()
    {
        // $packages = DB::select(
        //     "SELECT package.id, package.description, pack_dets.cl2comb, item.cl2desc, pack_dets.quantity, package.status
        //         FROM csrw_packages AS package
        //         JOIN csrw_package_details as pack_dets ON pack_dets.package_id = package.id
        //         JOIN hclass2 as item ON item.cl2comb = pack_dets.cl2comb
        //         -- WHERE wardcode = ?
        //         ORDER BY item.cl2desc ASC;",
        // );

        $packages = collect(DB::select(
            "SELECT
                package.id,
                package.description,
                pack_dets.cl2comb as item_id,
                CASE
                    WHEN item.cl2desc IS NOT NULL THEN item.cl2desc
                    WHEN misc.hmdesc IS NOT NULL THEN misc.hmdesc
                    ELSE 'Unknown Item'
                END as item_description,
                pack_dets.quantity,
                package.status,
                CASE
                    WHEN item.cl2desc IS NOT NULL THEN 'item'
                    WHEN misc.hmdesc IS NOT NULL THEN 'misc'
                    ELSE 'unknown'
                END as item_type
            FROM csrw_packages AS package
            JOIN csrw_package_details as pack_dets ON pack_dets.package_id = package.id
            LEFT JOIN hclass2 as item ON item.cl2comb = pack_dets.cl2comb
            LEFT JOIN hmisc as misc ON misc.hmcode = pack_dets.cl2comb AND misc.hmstat = 'A'
            WHERE (item.cl2comb IS NOT NULL OR misc.hmcode IS NOT NULL)
            ORDER BY package.description, item_description ASC"
        ));

        // dd($packages);

        return response()->json($packages);
    }

    public function store(Request $request)
    {
        // dd($request);
        $request->validate([
            'description' => 'required|max:150',
            'status' => 'required',
            'packageItems' => 'required|array|min:1',
        ]);

        // session()->forget([
        //     'cached_inertia_packages',
        // ]);

        $package = Package::create([
            'description' => $request->description,
            'status' => $request->status,
            'created_by' => $request->user,
            'wardcode' => $request->wardcode
        ]);
        $package_id = $package->id;

        foreach ($request->packageItems as $item) {
            // dd($item);
            PackageDetails::create([
                'cl2comb' => $item['id'],
                'quantity' => $item['quantity'],
                'package_id' => $package_id,
            ]);
        }

        return Redirect::route('packages.index');
    }

    public function update(Package $package, Request $request)
    {
        $request->validate([
            'description' => 'required|max:150',
            'status' => 'required',
            'packageItems' => 'required|array|min:1',
        ]);

        // session()->forget([
        //     'cached_inertia_packages',
        // ]);

        // Update the package
        $package = Package::where('id', $request->package_id)->update([
            'description' => $request->description,
            'status' => $request->status,
            'updated_by' => $request->user,
            'wardcode' => $request->wardcode
        ]);

        $packageId = $request->package_id;
        $newItems = collect($request->packageItems);

        // Get current package details
        $existingItems = DB::table('csrw_package_details')
            ->where('package_id', $packageId)
            ->get();

        // Convert to associative array for easy comparison
        $existingItemsMap = $existingItems->mapWithKeys(function ($item) {
            return [$item->cl2comb => $item];
        });

        // Process new items
        foreach ($newItems as $newItem) {
            if (isset($existingItemsMap[$newItem['id']])) {
                // Update quantity if changed
                if ($existingItemsMap[$newItem['id']]->quantity != $newItem['quantity']) {
                    DB::table('csrw_package_details')
                        ->where('package_id', $packageId)
                        ->where('cl2comb', $newItem['id'])
                        ->update([
                            'quantity' => $newItem['quantity'],
                            'updated_at' => Carbon::now(),
                        ]);
                }
                // Remove from existing items map to track remaining items
                unset($existingItemsMap[$newItem['id']]);
            } else {
                // Insert new item
                DB::table('csrw_package_details')->insert([
                    'package_id' => $packageId,
                    'cl2comb' => $newItem['id'],
                    'quantity' => $newItem['quantity'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        // Remove items that are no longer in the new list
        if ($existingItemsMap->isNotEmpty()) {
            DB::table('csrw_package_details')
                ->where('package_id', $packageId)
                ->whereIn('cl2comb', $existingItemsMap->keys())
                ->delete();
        }

        return Redirect::route('packages.index');
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return Redirect::route('packages.index');
    }
}
