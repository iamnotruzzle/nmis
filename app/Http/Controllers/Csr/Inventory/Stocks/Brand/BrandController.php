<?php

namespace App\Http\Controllers\Csr\Inventory\Stocks\Brand;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class BrandController extends Controller
{

    public function index()
    {
        //
    }


    public function store(Request $request)
    {
        // dd($request);

        $request->validate([
            'name' => 'required|unique:csrw_brands,name',
            'status' => 'required',
        ]);

        $brand = Brand::create([
            'name' => $request->name,
            'status' => $request->status,
        ]);

        return Redirect::route('csrstocks.index');
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:csrw_brands,name,' . $request->id,
            'status' => 'required',
        ]);

        Brand::where('id', $request->id)
            ->update([
                'name' => $request->name,
                'status' => $request->status,
            ]);

        return Redirect::route('csrstocks.index');
    }


    public function destroy($id)
    {
        //
    }
}
