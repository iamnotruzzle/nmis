<?php

namespace App\Http\Controllers\Wards\WardsManualReport;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\WardsManualReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class WardsManualReportController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        // get auth wardcode
        $authWardcode = DB::select(
            "SELECT TOP 1
                l.wardcode
            FROM
                user_acc u
            INNER JOIN
                csrw_login_history l ON u.employeeid = l.employeeid
            WHERE
                l.employeeid = ?
            ORDER BY
                l.created_at DESC;
            ",
            [Auth::user()->employeeid]
        );
        $authCode = $authWardcode[0]->wardcode;

        $manual_reports = WardsManualReport::with('item_description:cl2comb,cl2desc', 'unit:uomcode,uomdesc', 'entryBy:employeeid,firstname,lastname', 'updatedBy:employeeid,firstname,lastname', 'ward:wardcode,wardname')
            ->whereHas('item_description', function ($q) use ($searchString) {
                $q->where('cl2desc', 'LIKE', '%' . $searchString . '%');
            })
            ->when(
                $request->from,
                function ($query, $value) use ($from) {
                    $query->whereDate('created_at', '>=', $from);
                }
            )
            ->when(
                $request->to,
                function ($query, $value) use ($to) {
                    $query->whereDate('created_at', '<=', $to);
                }
            )
            ->where('wardcode', '=', $authCode)
            ->paginate(15);

        $items = Item::with('unit:uomcode,uomdesc')->where('cl2stat', 'A')->orderBy('cl2desc', 'ASC')->get();

        return Inertia::render('Wards/ManualReport/Index', [
            'manual_reports' => $manual_reports,
            'items' => $items
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cl2comb' => 'required',
            'unit_cost' => 'nullable|numeric|min:0',
            'esti_budg_unit_cost' => 'nullable|numeric|min:0',
            'beginning_balance' => 'nullable|numeric|min:0',
            'received_from_csr' => 'nullable|numeric|min:0',
            'total_stock' => 'nullable|numeric|min:0',
            'consumption_surgery' => 'nullable|numeric|min:0',
            'consumption_ob_gyne' => 'nullable|numeric|min:0',
            'consumption_ortho' => 'nullable|numeric|min:0',
            'consumption_pedia' => 'nullable|numeric|min:0',
            'consumption_optha' => 'nullable|numeric|min:0',
            'consumption_ent' => 'nullable|numeric|min:0',
            'total_consumption_quantity' => 'nullable|numeric|min:0',
            'total_consumption_cost' => 'nullable|numeric|min:0',
            'ending_balance' => 'nullable|numeric|min:0',
            'actual_inventory' => 'nullable|numeric|min:0',
        ]);

        $csrManualReport = WardsManualReport::create([
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'unit_cost' => $request->unit_cost,
            'esti_budg_unit_cost' => $request->esti_budg_unit_cost,
            'beginning_balance' => $request->beginning_balance,
            'received_from_csr' => $request->received_from_csr,
            'total_stock' => $request->total_stock,
            'consumption_surgery' => $request->consumption_surgery,
            'consumption_ob_gyne' => $request->consumption_ob_gyne,
            'consumption_ortho' => $request->consumption_ortho,
            'consumption_pedia' => $request->consumption_pedia,
            'consumption_optha' => $request->consumption_optha,
            'consumption_ent' => $request->consumption_ent,
            'total_consumption_quantity' => $request->total_consumption_quantity,
            'total_consumption_cost' => $request->total_consumption_cost,
            'ending_balance' => $request->ending_balance,
            'actual_inventory' => $request->actual_inventory,
            'wardcode' => $request->wardcode,
            'entry_by' => $request->entry_by,
        ]);

        return Redirect::route('wardsmanualreports.index');
    }

    public function update(WardsManualReport $wardsmanualreport, Request $request)
    {
        $request->validate([
            'cl2comb' => 'required',
            'unit_cost' => 'nullable|numeric|min:0',
            'esti_budg_unit_cost' => 'nullable|numeric|min:0',
            'beginning_balance' => 'nullable|numeric|min:0',
            'received_from_csr' => 'nullable|numeric|min:0',
            'total_stock' => 'nullable|numeric|min:0',
            'consumption_surgery' => 'nullable|numeric|min:0',
            'consumption_ob_gyne' => 'nullable|numeric|min:0',
            'consumption_ortho' => 'nullable|numeric|min:0',
            'consumption_pedia' => 'nullable|numeric|min:0',
            'consumption_optha' => 'nullable|numeric|min:0',
            'consumption_ent' => 'nullable|numeric|min:0',
            'total_consumption_quantity' => 'nullable|numeric|min:0',
            'total_consumption_cost' => 'nullable|numeric|min:0',
            'ending_balance' => 'nullable|numeric|min:0',
            'actual_inventory' => 'nullable|numeric|min:0',
        ]);

        $wardsmanualreport->update([
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'unit_cost' => $request->unit_cost,
            'esti_budg_unit_cost' => $request->esti_budg_unit_cost,
            'beginning_balance' => $request->beginning_balance,
            'received_from_csr' => $request->received_from_csr,
            'total_stock' => $request->total_stock,
            'consumption_surgery' => $request->consumption_surgery,
            'consumption_ob_gyne' => $request->consumption_ob_gyne,
            'consumption_ortho' => $request->consumption_ortho,
            'consumption_pedia' => $request->consumption_pedia,
            'consumption_optha' => $request->consumption_optha,
            'consumption_ent' => $request->consumption_ent,
            'total_consumption_quantity' => $request->total_consumption_quantity,
            'total_consumption_cost' => $request->total_consumption_cost,
            'ending_balance' => $request->ending_balance,
            'actual_inventory' => $request->actual_inventory,
            'updated_by' => $request->updated_by,
        ]);

        return Redirect::route('wardsmanualreports.index');
    }

    public function destroy(WardsManualReport $wardsmanualreport)
    {
        $wardsmanualreport->delete();

        return Redirect::route('wardsmanualreports.index');
    }
}
