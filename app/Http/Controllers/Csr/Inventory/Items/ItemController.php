<?php

namespace App\Http\Controllers\Csr\Inventory\Items;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index()
    {
        $cl1combs = Category::where('cl1stat', 'A')
            ->orderBy('cl1comb', 'ASC')
            ->get('cl1comb');

        return Inertia::render('Csr/Inventory/Items/Index', [
            'cl1combs' => $cl1combs,
        ]);
    }

    public function store(Request $request)
    {
        // $request->validate([
        //     'ptcode' => 'required|max:5',
        //     'cl1code' => 'required|unique:hclass1,cl1code|max:5',
        //     'cl1desc' => 'required|max:20',
        //     'cl1stat' => 'required|max:1',
        //     'cl1upsw' => 'required|max:1',
        // ]);

        // $categories = Category::create([
        //     'ptcode' => $request->ptcode,
        //     'cl1code' => $request->cl1code,
        //     'cl1comb' => $request->ptcode . '' . $request->cl1code,
        //     'cl1desc' => $request->cl1desc,
        //     'cl1stat' => $request->cl1stat,
        //     'cl1lock' => 'N',
        //     'cl1upsw' => $request->cl1upsw,
        //     'cl1dtmd' => NULL,
        //     'compense' => NULL,
        // ]);

        return Redirect::route('items.index');
    }

    public function update(Item $item, Request $request)
    {
        // $request->validate([
        //     'ptcode' => 'required|max:5',
        //     'cl1code' => 'required|unique:hclass1,cl1code|max:5',
        //     'cl1desc' => 'required|max:20',
        //     'cl1stat' => 'required|max:1',
        //     'cl1upsw' => 'required|max:1',
        // ]);

        // $item->update([
        //     'ptcode' => $request->ptcode,
        //     'cl1code' => $request->cl1code,
        //     'cl1comb' => $request->ptcode . '' . $request->cl1code,
        //     'cl1desc' => $request->cl1desc,
        //     'cl1stat' => $request->cl1stat,
        //     'cl1lock' => 'N',
        //     'cl1upsw' => $request->cl1upsw,
        //     'cl1dtmd' => NULL,
        //     'compense' => NULL,
        // ]);

        return Redirect::route('items.index');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return Redirect::route('items.index');
    }
}
