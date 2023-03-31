<?php

namespace App\Http\Controllers\Csr\Inventory\Categories;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {


        $categories = Category::when($request->search, function ($query, $value) {
            $query->where('cl1comb', 'LIKE', '%' . $value . '%')
                ->orWhere('cl1desc', 'LIKE', '%' . $value . '%');
        })
            ->where('cl1stat', 'A')
            ->orderBy('cl1desc', 'ASC')
            ->paginate(15);

        return Inertia::render('Csr/Inventory/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        //
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
