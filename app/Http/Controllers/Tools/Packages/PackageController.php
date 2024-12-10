<?php

namespace App\Http\Controllers\Tools\Packages;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Package;
use App\Models\PackageDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PackageController extends Controller
{
    public function index()
    {
        $items = Item::where('cl2stat', 'A')
            ->orderBy('cl2desc', 'ASC')
            ->get(['cl2comb', 'cl2desc']);
        // dd($items);

        return Inertia::render('Tools/Packages/Index', [
            'items' => $items,
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

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
