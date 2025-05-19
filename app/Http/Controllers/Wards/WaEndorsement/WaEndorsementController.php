<?php

namespace App\Http\Controllers\Wards\WaEndorsement;

use App\Http\Controllers\Controller;
use App\Models\UserDetail;
use App\Models\WaEndorsement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WaEndorsementController extends Controller
{
    public function index()
    {
        $endorsements = WaEndorsement::with(['details', 'form_user', 'to_user'])
            ->paginate(5);

        $employees = UserDetail::where('empstat', 'A')->orderBy('employeeid', 'ASC')->get(['employeeid', 'empstat', 'firstname', 'lastname']);

        return Inertia::render('Wards/WaEndorsements/Index', [
            'endorsements' => $endorsements,
            'employees' => $employees,
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
