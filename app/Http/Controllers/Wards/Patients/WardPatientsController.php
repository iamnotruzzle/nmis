<?php

namespace App\Http\Controllers\Wards\Patients;

use App\Http\Controllers\Controller;
use App\Models\PatientRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WardPatientsController extends Controller
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

        $patients = PatientRoom::with([
            // when selecting an eager load table,
            // don't forget to get the foreign key as well
            'patient:patfirst,patmiddle,patlast,patsuffix,hpercode',
            'ward:wardcode,wardname,wclcode,wardstat',
            'room:rmintkey,wardcode,rmcode,rmname,rmbed,rmstat',
            'bed:bdintkey,wardcode,rmintkey,bdcode,bdname,bdstat'
        ])
            // this will filter patients that hasn't been discharge
            ->whereHas('patient.admission', function ($q) {
                $q->where('disdate', null);
            })
            ->whereHas('patient', function ($q) use ($searchString) {
                $q->where('patfirst', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('patmiddle', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('patlast', 'LIKE', '%' . $searchString . '%');
            })
            ->where('patrmstat', 'a')
            ->where('wardcode', $authWardcode->wardcode)
            // order by patient
            ->join('hperson', 'hpatroom.hpercode', '=', 'hperson.hpercode')->orderBy('hperson.patlast', 'ASC')->select('hpatroom.*')
            ->paginate(8);

        // dd($patients);

        return Inertia::render('Wards/Patients/Index', [
            'patients' => $patients
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
