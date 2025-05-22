<?php

namespace App\Http\Controllers\Wards\WaEndorsement;

use App\Http\Controllers\Controller;
use App\Models\UserDetail;
use App\Models\WaEndorsement;
use App\Models\WaEndorsementDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class WaEndorsementController extends Controller
{
    public function index(Request $request)
    {
        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        //region get auth ward code
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
        // dd($authWardcode);
        $authCode = $authWardcode[0]->wardcode;

        $endorsements = WaEndorsement::with([
            'ward:wardcode,wardname',
            'details',
            'from_user:employeeid,firstname,middlename,lastname',
            'to_user:employeeid,firstname,middlename,lastname'
        ])
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
            ->where('wardcode', $authCode)
            ->where('soft_delete', null)
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        $employees = UserDetail::where('empstat', 'A')->orderBy('employeeid', 'ASC')->get(['employeeid', 'empstat', 'firstname', 'lastname']);

        return Inertia::render('Wards/WaEndorsements/Index', [
            'endorsements' => $endorsements,
            'employees' => $employees,
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $endorsement = WaEndorsement::create([
                    'from_user' => $request->from_user,
                    'to_user' => $request->to_user,
                    'wardcode' => $request->wardcode,
                ]);

                foreach ($request->endorsementDetails as $detail) {
                    WaEndorsementDetail::create([
                        'endorsement_id' => $endorsement->id,
                        'description' => $detail['description'],
                        'tag' => $detail['tag'],
                        'status' => $detail['status'],
                    ]);
                }
            });
        } catch (Throwable $e) {
            Log::error('Failed to create endorsement: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to create endorsement. Please try again.',
            ], 500);
        }

        return redirect()->back()->with('success', 'Endorsement saved successfully.');
    }

    public function update(WaEndorsement $waendorsement, Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $id = $request->id;

                $updated = WaEndorsement::where('id', $id)
                    ->update([
                        'to_user' => $request->to_user,
                    ]);

                // Optionally check if update actually happened
                if ($updated === 0) {
                    throw new \Exception("Failed to update WaEndorsement with ID {$id}");
                }

                WaEndorsementDetail::where('endorsement_id', $id)->delete();

                foreach ($request->endorsementDetails as $e) {
                    WaEndorsementDetail::create([
                        'endorsement_id' => $id,
                        'tag' => $e['tag'],
                        'description' => $e['description'],
                        'status' => $e['status'],
                    ]);
                }
            });
        } catch (Throwable $e) {
            Log::error('Failed to update endorsement transaction: ' . $e->getMessage());
            return response()->json(['message' => 'Something went wrong. Please try again.'], 500);
        }

        return redirect()->back()->with('success', 'Endorsement updated successfully.');
    }

    public function destroy(WaEndorsement $waendorsement, Request $request)
    {

        // dd($requeststock->id);
        $id = $request->id;

        // delete request stock
        // $requeststock->delete();

        // // delete request stock details
        // RequestStocksDetails::where('request_stocks_id', $requestStocksID)->delete();

        WaEndorsement::where('id', $id)
            ->update([
                'soft_delete' => 'yes',
            ]);

        return redirect()->back()->with('success', 'Endorsement cancelled.');
    }
}
