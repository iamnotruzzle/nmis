<?php

namespace App\Http\Controllers\Wards\WaEndorsement;

use App\Http\Controllers\Controller;
use App\Models\UserDetail;
use App\Models\WaEndorsement;
use App\Models\WaEndorsementDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class WaEndorsementController extends Controller
{
    public function index()
    {
        $endorsements = WaEndorsement::with([
            'ward:wardcode,wardname',
            'details',
            'from_user:employeeid,firstname,middlename,lastname',
            'to_user:employeeid,firstname,middlename,lastname'
        ])
            ->paginate(5);
        // dd($endorsements);

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

    public function destroy($id)
    {
        //
    }
}
