<?php

namespace App\Http\Controllers\Csr\Inventory\Categories\SubCategory;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SubCategoryController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        // dd($request);

        $request->validate([
            'ptcode' => 'required',
            'cl1code' => 'required|unique:hclass1,cl1code|max:5',
            'cl1desc' => 'required|unique:hclass1,cl1desc|max:25',
            'cl1stat' => 'required',
        ]);

        $categories = Category::create([
            'cl1comb' => $request->ptcode . $request->cl1code,
            'ptcode' => $request->ptcode,
            'cl1code' => $request->cl1code,
            'cl1desc' => $request->cl1desc,
            'cl1stat' => $request->cl1stat,
            'cl1lock' => 'N',
            'cl1upsw' => 'P',
            'cl1dtmd' => NULL,
            'compense' => NULL,
        ]);

        // dd($categories);

        return Redirect::route('categories.index');
    }

    public function update(Category $subCategory, Request $request)
    {
        // category
        // $cat = ProcTypeForHclass::where('ptcode', $request->ptcode)->first();

        $request->validate([
            'ptcode' => 'required',
            'cl1code' => [
                'required',
                'max:5',
                rule::unique('hclass1')->ignore($request->cl1code, 'cl1code')
            ],
            'cl1desc' => [
                'required',
                'max:25',
                rule::unique('hclass1')->ignore($request->cl1desc, 'cl1desc')
            ],
            'cl1stat' => 'required',
        ]);

        Category::where('cl1comb', $request->cl1comb)
            ->update([
                'cl1comb' => $request->ptcode . $request->cl1code,
                'ptcode' => $request->ptcode,
                'cl1code' => $request->cl1code,
                'cl1desc' => $request->cl1desc,
                'cl1stat' => $request->cl1stat,
                'cl1lock' => 'N',
                'cl1upsw' => 'P',
                'cl1dtmd' => NULL,
                'compense' => NULL,
            ]);

        return Redirect::route('categories.index');
    }

    public function destroy(Category $subCategory, Request $request)
    {
        Category::where('cl1comb', $request->cl1comb)->delete();

        return Redirect::route('categories.index');
    }
}
