<?php

namespace App\Http\Controllers\Wards\WardsManualReport;

use App\Http\Controllers\Controller;
use App\Models\WardsManualReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WardsManualReportController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $manual_reports = WardsManualReport::with('item_description:cl2comb,cl2desc', 'unit:uomcode,uomdesc', 'entryBy:employeeid,firstname,lastname', 'updatedBy:employeeid,firstname,lastname', 'ward:wardcode,wardname')
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
            ->where('wardcode', '=', $authWardcode->wardcode)
            ->paginate(15);

        return Inertia::render('Wards/ManualReport/Index', [
            'manual_reports' => $manual_reports
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
