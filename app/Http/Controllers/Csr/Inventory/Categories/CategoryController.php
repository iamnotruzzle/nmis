<?php

namespace App\Http\Controllers\Csr\Inventory\Categories;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ProcTypeForHclass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        //   check session
        $hasSession = Sessions::where('id', Session::getId())->exists();

        if ($hasSession) {
            $user = Auth::user();

            $authWardcode = DB::table('csrw_users')
                ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
                ->select('csrw_login_history.wardcode')
                ->where('csrw_login_history.employeeid', $user->employeeid)
                ->orderBy('csrw_login_history.created_at', 'desc')
                ->first();


            Sessions::where('id', Session::getId())->update([
                // 'user_id' => $request->login,
                'location' => $authWardcode->wardcode,
            ]);
        }
        // end check session

        $catID = $request->catID;

        // $csrSuppliesSubCategory = Category::with('pims_category:id,catID,categoryname')
        //     ->has('pims_category')
        //     ->when($catID, function ($query) use ($catID) {
        //         $query->whereHas('pims_category', function ($q) use ($catID) {
        //             $q->where('catID', $catID);
        //         });
        //     })
        //     ->when($request->search, function ($query, $value) {
        //         $query->where('cl1desc', 'LIKE', '%' . $value . '%')
        //             ->orWhere('cl1comb', 'LIKE', '%' . $value . '%');
        //     })
        //     ->when(
        //         $request->status,
        //         function ($query, $value) {
        //             $query->where('cl1stat', $value);
        //         }
        //     )
        //     ->where('ptcode', '1000')
        //     ->orderBy('cl1desc', 'ASC')
        //     ->paginate(15);
        // dd($csrSuppliesSubCategory);

        $csrSuppliesSubCategory = DB::select(
            "SELECT sub_category.cl1comb, main_category.catID, main_category.categoryname, sub_category.cl1desc, sub_category.cl1stat
                FROM hclass1 as sub_category
                JOIN csrw_pims_categories as main_category ON sub_category.catID = main_category.catID
                WHERE sub_category.ptcode = '1000'
                ORDER BY sub_category.cl1desc ASC;"
        );

        // dd($csrSuppliesSubCategory);

        return Inertia::render('Csr/Inventory/Categories/Index', [
            'csrSuppliesSubCategory' => $csrSuppliesSubCategory,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request);

        $request->validate([
            'description' => 'required|min:4',
            'status' => 'required',
            'category' => 'required',
        ]);

        // generate unique cl1code
        $cl1code = 'p' . Str::random(5);
        // dd($cl1code);

        $subCategory = Category::firstOrCreate([
            'cl1comb' => '1000' . '-' . $cl1code,
            'ptcode' => '1000',
            'cl1code' => $cl1code,
            'cl1desc' => trim($request->description),
            'cl1stat' => $request->status,
            'cl1lock' => 'N',
            'cl1upsw' => 'P',
            'cl1dtmd' => NULL,
            'compense' => NULL,
            'catID' => $request->category,
        ]);

        return Redirect::route('categories.index');
    }

    public function update(Category $category, Request $request)
    {
        $request->validate([
            'description' => [
                'required',
                'min:4'
            ],
            'status' => 'required',
            'category' => 'required',
        ]);

        $category->update([
            'cl1desc' => trim($request->description),
            'cl1stat' => $request->status,
            'catID' => $request->category,
        ]);

        return Redirect::route('categories.index');
    }

    public function destroy(Category $category)
    {
        // dd($category);
        $category->delete();

        return Redirect::route('categories.index');
    }
}
