<?php

namespace App\Http\Controllers\Tools\Packages;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Package;
use App\Models\PackageDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PackageController extends Controller
{
    public function index()
    {
        $items = Item::where('cl2stat', 'A')
            ->orderBy('cl2desc', 'ASC')
            ->get(['cl2comb', 'cl2desc']);

        $packages = DB::select(
            "SELECT package.id, package.description, pack_dets.cl2comb, item.cl2desc, pack_dets.quantity, package.status
                FROM csrw_packages AS package
                JOIN csrw_package_details as pack_dets ON pack_dets.package_id = package.id
                JOIN hclass2 as item ON item.cl2comb = pack_dets.cl2comb
                ORDER BY item.cl2desc ASC;"
        );

        return Inertia::render('Tools/Packages/Index', [
            'items' => $items,
            'packages' => $packages,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->packageItems);

        $request->validate([
            'description' => 'required|max:150',
            'status' => 'required',
            'packageItems' => 'required|array|min:1',
        ]);

        $package = Package::create([
            'description' => $request->description,
            'status' => $request->status,
        ]);
        $package_id = $package->id;

        foreach ($request->packageItems as $item) {
            // dd($item);
            PackageDetails::create([
                'cl2comb' => $item['cl2comb'],
                'quantity' => $item['quantity'],
                'package_id' => $package_id,
            ]);
        }

        return Redirect::route('packages.index');
    }

    public function update(Package $package, Request $request)
    {

        $request->validate([
            'cl1comb' => 'required|max:20',
            'cl2desc' => 'required',
            'unit' => 'required',
            'cl2stat' => 'required|max:1',
        ]);

        $updated_item =  $item->update([
            'catID' => $request->mainCategory, // main category
            'cl2comb' => trim($request->cl1comb) . '-' . trim($request->cl2code),
            'cl1comb' => trim($request->cl1comb), // sub category
            'itemcode' => $request->itemcode,
            'cl2desc' => trim($request->cl2desc), // item desc
            'uomcode' => $request->unit, // unit
            'cl2stat' => $request->cl2stat,
        ]);

        return Redirect::route('packages.index');
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return Redirect::route('packages.index');
    }
}
