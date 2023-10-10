<?php

namespace App\Http\Controllers\Csr\Inventory\Categories;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProcTypeForHclass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $procTypes = ProcTypeForHclass::orderBy('ptdesc', 'ASC')
            ->get();

        $mainCategory = ProcTypeForHclass::with('subCategory')
            ->when($request->search, function ($query, $value) {
                $query->where('ptdesc', 'LIKE', '%' . $value . '%');
            })
            ->orderBy('dateasof', 'DESC')
            ->paginate(15);

        return Inertia::render('Csr/Inventory/Categories/Index', [
            'mainCategory' => $mainCategory,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'ptcode' => 'required|max:5',
            'cl1code' => 'required|unique:hclass1,cl1code|max:5',
            'cl1desc' => 'required|max:20',
            'cl1stat' => 'required|max:1',
            'cl1upsw' => 'required|max:1',
        ]);

        $categories = Category::create([
            'ptcode' => $request->ptcode,
            'cl1code' => $request->cl1code,
            'cl1comb' => $request->ptcode . '' . $request->cl1code,
            'cl1desc' => $request->cl1desc,
            'cl1stat' => $request->cl1stat,
            'cl1lock' => 'N',
            'cl1upsw' => $request->cl1upsw,
            'cl1dtmd' => NULL,
            'compense' => NULL,
        ]);

        return Redirect::route('categories.index');
    }

    public function update(Category $category, Request $request)
    {
        $request->validate([
            'ptcode' => 'required|max:5',
            'cl1code' => [
                'required',
                'max:5',
                Rule::unique('hclass1')->ignore($request->cl1code, 'cl1code') // 'cl1code' is the column
            ],
            'cl1desc' => 'required|max:20',
            'cl1stat' => 'required|max:1',
            'cl1upsw' => 'required|max:1',
        ]);

        $category->update([
            'ptcode' => $request->ptcode,
            'cl1code' => $request->cl1code,
            'cl1comb' => $request->ptcode . '' . $request->cl1code,
            'cl1desc' => $request->cl1desc,
            'cl1stat' => $request->cl1stat,
            'cl1lock' => 'N',
            'cl1upsw' => $request->cl1upsw,
            'cl1dtmd' => NULL,
            'compense' => NULL,
        ]);

        return Redirect::route('categories.index');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return Redirect::route('categories.index');
    }
}
