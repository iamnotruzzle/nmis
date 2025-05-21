<?php

namespace App\Http\Controllers\Wards\WaEndorsement;

use App\Http\Controllers\Controller;
use App\Models\UserDetail;
use App\Models\WaEndorsement;
use App\Models\WaEndorsementDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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
        // dd($request);

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

        return redirect()->back()->with('success', 'Endorsement saved successfully.');
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
