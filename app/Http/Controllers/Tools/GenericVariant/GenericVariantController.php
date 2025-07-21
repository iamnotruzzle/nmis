<?php

namespace App\Http\Controllers\Tools\GenericVariant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class GenericVariantController extends Controller
{
    public function index()
    {
        $variants = DB::table('csrw_generic_variants AS gv')
            ->join('hclass2 AS gen', 'gv.generic_cl2comb', '=', 'gen.cl2comb')
            ->join('hclass2 AS var', 'gv.variant_cl2comb', '=', 'var.cl2comb')
            ->select(
                'gv.id',
                'gv.generic_cl2comb',
                'gen.cl2desc AS generic_desc',
                'gv.variant_cl2comb',
                'var.cl2desc AS variant_desc'
            )
            ->orderBy('generic_desc')
            ->get();

        $items = DB::select(
            "SELECT cl2comb, cl2desc FROM hclass2 WHERE catID = 1 ORDER BY cl2desc ASC"
        );

        return Inertia::render('Tools/GenericVariants/Index', [
            'variants' => $variants,
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'generic_cl2comb' => 'required|string|max:50',
            'variant_cl2comb' => 'required|string|max:50',
        ]);

        DB::table('csrw_generic_variants')->insert([
            'generic_cl2comb' => $request->generic_cl2comb,
            'variant_cl2comb' => $request->variant_cl2comb,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Redirect::route('generic-variants.index');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'generic_cl2comb' => 'required|string|max:50',
            'variant_cl2comb' => 'required|string|max:50',
        ]);

        DB::table('csrw_generic_variants')
            ->where('id', $id)
            ->update([
                'generic_cl2comb' => $request->generic_cl2comb,
                'variant_cl2comb' => $request->variant_cl2comb,
                'updated_at' => now(),
            ]);

        return Redirect::route('generic-variants.index');
    }

    public function destroy($id)
    {
        DB::table('csrw_generic_variants')->where('id', $id)->delete();

        return Redirect::route('generic-variants.index');
    }
}
