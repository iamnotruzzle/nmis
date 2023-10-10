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
            'ptcode' => 'required||unique:hproctyp,ptcode|max:5',
            'ptdesc' => 'required|unique:hproctyp,ptdesc|max:30',
            'ptstat' => 'required',
        ]);

        $categories = ProcTypeForHclass::create([
            'ptcode' => $request->ptcode,
            'ptdesc' => $request->ptdesc,
            'ptstat' => $request->ptstat,
            'dateasof' => Carbon::now(),
            'ptlock' => 'N',
            'ptupsw' => 'P',
            'ptdtmd' => NULL,
            'chrgcode' => NULL,
        ]);

        return Redirect::route('categories.index');
    }

    public function update(ProcTypeForHclass $category, Request $request)
    {
        $request->validate([
            'ptcode' => 'required||unique:hproctyp,ptcode|max:5',
            'ptdesc' => 'required|unique:hproctyp,ptdesc|max:30',
            'ptstat' => 'required',
        ]);

        $category->update([
            'ptcode' => $request->ptcode,
            'ptdesc' => $request->ptdesc,
            'ptstat' => $request->ptstat,
            'dateasof' => Carbon::now(),
            'ptlock' => 'N',
            'ptupsw' => 'P',
            'ptdtmd' => NULL,
            'chrgcode' => NULL,
        ]);

        return Redirect::route('categories.index');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return Redirect::route('categories.index');
    }
}
