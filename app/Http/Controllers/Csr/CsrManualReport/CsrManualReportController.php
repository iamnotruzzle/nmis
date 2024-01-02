<?php

namespace App\Http\Controllers\Csr\CsrManualReport;

use App\Http\Controllers\Controller;
use App\Models\CsrManualReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class CsrManualReportController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;

        $manual_reports = CsrManualReport::with('item_description:cl2comb,cl2desc', 'unit:uomcode,uomdesc', 'entryBy:employeeid,firstname,lastname', 'updatedBy:employeeid,firstname,lastname')
            ->whereHas('item_description', function ($q) use ($searchString) {
                $q->where('cl2desc', 'LIKE', '%' . $searchString . '%');
            })
            ->when(
                $request->from,
                function ($query, $value) {
                    $query->whereDate('created_at', '>=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->to,
                function ($query, $value) {
                    $query->whereDate('created_at', '<=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->paginate(15);

        return Inertia::render('Csr/ManualReport/Index', [
            'manual_reports' => $manual_reports
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cl2comb' => 'required',
            'unit_cost' => 'nullable|numeric|min:0',
            'csr_beg_bal_quantity' => 'nullable|numeric|min:0',
            'csr_beg_bal_total_cost' => 'nullable|numeric|min:0',
            'wards_beg_bal_quantity' => 'nullable|numeric|min:0',
            'wards_beg_bal_total_cost' => 'nullable|numeric|min:0',
            'total_beg_bal_total_quantity' => 'nullable|numeric|min:0',
            'total_beg_bal_total_cost' => 'nullable|numeric|min:0',
            'supp_issued_to_wards_total_quantity' => 'nullable|numeric|min:0',
            'supp_issued_to_wards_total_cost' => 'nullable|numeric|min:0',
            'consumption_quantity' => 'nullable|numeric|min:0',
            'consumption_total_cost' => 'nullable|numeric|min:0',
            'csr_end_bal_quantity' => 'nullable|numeric|min:0',
            'csr_end_bal_total_cost' => 'nullable|numeric|min:0',
            'wards_end_bal_quantity' => 'nullable|numeric|min:0',
            'wards_end_bal_total_cost' => 'nullable|numeric|min:0',
            'total_end_bal_total_quantity' => 'nullable|numeric|min:0',
            'total_end_bal_total_cost' => 'nullable|numeric|min:0',
        ]);

        $csrManualReport = CsrManualReport::create([
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'unit_cost' => $request->unit_cost,
            'csr_beg_bal_quantity' => $request->csr_beg_bal_quantity,
            'csr_beg_bal_total_cost' => $request->csr_beg_bal_total_cost,
            'wards_beg_bal_quantity' => $request->wards_beg_bal_quantity,
            'wards_beg_bal_total_cost' => $request->wards_beg_bal_total_cost,
            'total_beg_bal_total_quantity' => $request->total_beg_bal_total_quantity,
            'total_beg_bal_total_cost' => $request->total_beg_bal_total_cost,
            'supp_issued_to_wards_total_quantity' => $request->supp_issued_to_wards_total_quantity,
            'supp_issued_to_wards_total_cost' => $request->supp_issued_to_wards_total_cost,
            'consumption_quantity' => $request->consumption_quantity,
            'consumption_total_cost' => $request->consumption_total_cost,
            'csr_end_bal_quantity' => $request->csr_end_bal_quantity,
            'csr_end_bal_total_cost' => $request->csr_end_bal_total_cost,
            'wards_end_bal_quantity' => $request->wards_end_bal_quantity,
            'wards_end_bal_total_cost' => $request->wards_end_bal_total_cost,
            'total_end_bal_total_quantity' => $request->total_end_bal_total_quantity,
            'total_end_bal_total_cost' => $request->total_end_bal_total_cost,
            'entry_by' => $request->entry_by,
        ]);

        return Redirect::route('csrmanualreports.index');
    }

    public function update(CsrManualReport $csrmanualreport, Request $request)
    {
        $request->validate([
            'cl2comb' => 'required',
            'unit_cost' => 'nullable|numeric|min:0',
            'csr_beg_bal_quantity' => 'nullable|numeric|min:0',
            'csr_beg_bal_total_cost' => 'nullable|numeric|min:0',
            'wards_beg_bal_quantity' => 'nullable|numeric|min:0',
            'wards_beg_bal_total_cost' => 'nullable|numeric|min:0',
            'total_beg_bal_total_quantity' => 'nullable|numeric|min:0',
            'total_beg_bal_total_cost' => 'nullable|numeric|min:0',
            'supp_issued_to_wards_total_quantity' => 'nullable|numeric|min:0',
            'supp_issued_to_wards_total_cost' => 'nullable|numeric|min:0',
            'consumption_quantity' => 'nullable|numeric|min:0',
            'consumption_total_cost' => 'nullable|numeric|min:0',
            'csr_end_bal_quantity' => 'nullable|numeric|min:0',
            'csr_end_bal_total_cost' => 'nullable|numeric|min:0',
            'wards_end_bal_quantity' => 'nullable|numeric|min:0',
            'wards_end_bal_total_cost' => 'nullable|numeric|min:0',
            'total_end_bal_total_quantity' => 'nullable|numeric|min:0',
            'total_end_bal_total_cost' => 'nullable|numeric|min:0',
        ]);

        $updated_by = Auth::user()->employeeid;

        $csrmanualreport->update([
            'cl2comb' => $request->cl2comb,
            'uomcode' => $request->uomcode,
            'unit_cost' => $request->unit_cost,
            'csr_beg_bal_quantity' => $request->csr_beg_bal_quantity,
            'csr_beg_bal_total_cost' => $request->csr_beg_bal_total_cost,
            'wards_beg_bal_quantity' => $request->wards_beg_bal_quantity,
            'wards_beg_bal_total_cost' => $request->wards_beg_bal_total_cost,
            'total_beg_bal_total_quantity' => $request->total_beg_bal_total_quantity,
            'total_beg_bal_total_cost' => $request->total_beg_bal_total_cost,
            'supp_issued_to_wards_total_quantity' => $request->supp_issued_to_wards_total_quantity,
            'supp_issued_to_wards_total_cost' => $request->supp_issued_to_wards_total_cost,
            'consumption_quantity' => $request->consumption_quantity,
            'consumption_total_cost' => $request->consumption_total_cost,
            'csr_end_bal_quantity' => $request->csr_end_bal_quantity,
            'csr_end_bal_total_cost' => $request->csr_end_bal_total_cost,
            'wards_end_bal_quantity' => $request->wards_end_bal_quantity,
            'wards_end_bal_total_cost' => $request->wards_end_bal_total_cost,
            'total_end_bal_total_quantity' => $request->total_end_bal_total_quantity,
            'total_end_bal_total_cost' => $request->total_end_bal_total_cost,
            'updated_by' => $request->updated_by,
        ]);

        return Redirect::route('csrmanualreports.index');
    }

    public function destroy(CsrManualReport $csrmanualreport)
    {
        $csrmanualreport->delete();

        return Redirect::route('csrmanualreports.index');
    }
}
