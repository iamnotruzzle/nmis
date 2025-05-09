<?php

namespace App\Http\Controllers\Wards\ReOrderLevel;

use App\Http\Controllers\Controller;
use App\Models\WardsReOrderLevel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ReOrderLevelController extends Controller
{
    public function index()
    {
        $authWardCode_cached = Cache::get('c_authWardCode_' . Auth::user()->employeeid);
        $wardCode = $authWardCode_cached;

        $items = DB::select(
            "SELECT
                item.cl2comb,
                item.cl2desc,
                item.uomcode,
                uom.uomdesc
            FROM
                hclass2 AS item
           JOIN huom AS uom
                ON uom.uomcode = item.uomcode
            WHERE
                (item.catID = 1
                AND item.uomcode != 'box'
                AND (item.itemcode NOT LIKE 'MSMG-%' OR item.itemcode IS NULL))
            ORDER BY
                item.cl2desc ASC;"
        );

        $itemsWithReOrderLevel = DB::select(
            "SELECT lvl.id, lvl.cl2comb, item.cl2desc, uom.uomcode, uom.uomdesc, lvl.reorder_point, lvl.reorder_quantity, lvl.status, lvl.wardcode, ward.wardname,
                employee.firstname + ' ' + employee.lastname AS created_by_name, employee.firstname + ' ' + employee.lastname AS updated_by_name,
                lvl.created_at
                FROM csrw_wards_stock_level as lvl
                JOIN hclass2 as item ON item.cl2comb = lvl.cl2comb
                JOIN hward as ward ON ward.wardcode = lvl.wardcode
                JOIN huom AS uom ON uom.uomcode = item.uomcode
                JOIN hpersonal as employee ON employee.employeeid = lvl.created_by
                LEFT JOIN hpersonal AS updated_by_employee ON updated_by_employee.employeeid = lvl.updated_by
                WHERE lvl.wardcode = ?;",
            [$wardCode]
        );

        return Inertia::render('Wards/ReOrderLevel/Index', [
            'items' => $items,
            'itemsWithReOrderLevel' => $itemsWithReOrderLevel,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request);

        $cache_authWardCode = 'c_authWardCode_' . Auth::user()->employeeid;
        $authWardCode_cached = Cache::get($cache_authWardCode);

        $reOrderLevel = WardsReOrderLevel::create([
            'cl2comb' => $request->cl2comb,
            'reorder_point' => $request->reorder_point,
            'reorder_quantity' => $request->reorder_quantity,
            'status' => $request->status,
            'wardcode' => $authWardCode_cached,
            'created_by' => Auth::user()->employeeid,
        ]);

        return Redirect::route('reorder.index');
    }

    public function update(WardsReOrderLevel $reorder, Request $request)
    {
        $reorder->update([
            'reorder_point' => $request->reorder_point,
            'reorder_quantity' => $request->reorder_quantity,
            'status' => $request->status,
            'updated_by' => Auth::user()->employeeid,
        ]);

        return Redirect::route('reorder.index');
    }

    public function destroy($id)
    {
        //
    }
}
