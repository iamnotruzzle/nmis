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
        $csrSuppliesSubCategory = Category::when($request->search, function ($query, $value) {
            $query->where('cl1desc', 'LIKE', '%' . $value . '%')
                ->orWhere('cl1comb', 'LIKE', '%' . $value . '%');
        })
            ->when(
                $request->status,
                function ($query, $value) {
                    $query->where('cl1stat', $value);
                }
            )
            ->where('ptcode', '1000')
            ->orderBy('cl1desc', 'ASC')
            ->paginate(15, ['cl1comb', 'cl1desc', 'cl1stat']);
        // dd($csrSuppliesSubCategory);

        return Inertia::render('Csr/Inventory/Categories/Index', [
            'csrSuppliesSubCategory' => $csrSuppliesSubCategory,
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
        // category
        $cat = ProcTypeForHclass::where('ptcode', $request->ptcode)->first();

        $request->validate([
            // 'ptcode' => [
            //     'required',
            //     'max:5',
            //     Rule::unique('hproctyp')->ignore($request->ptcode, 'ptcode')
            // ],
            'ptdesc' => [
                'required',
                'max:30',
                Rule::unique('hproctyp')->ignore($request->ptdesc, 'ptdesc')
            ],
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

    public function destroy(ProcTypeForHclass $category)
    {
        $category->delete();

        return Redirect::route('categories.index');
    }
}
