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

        // $sort_by = '';
        $order_by = $request->order_by;

        if ($request->order_by == '' || $request->order_by == null) {
            $order_by = 'asc';
        }

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $patients = PatientRoom::with([
            // REMOVE SOME RELATIONSHIP BECAUSE IT's ALREADY REDUNDANT
            // 'patient',
            // 'patient.doctorOrder',
            'patient.doctorOrder.docOrderType',
            'patient.admission:enccode,disdate',
            // 'patient.admissionDate',
            'patient.admissionDate.physician.hpersonal:employeeid,lastname,firstname,middlename',
            'patient.admissionDate.physician2.hpersonal:employeeid,lastname,firstname,middlename',
            'patient.admissionDate.physician3.hpersonal:employeeid,lastname,firstname,middlename',
            'patient.admissionDate.physician4.hpersonal:employeeid,lastname,firstname,middlename',
            // 'patient.admissionDate.doctorOrder',
            'patient.admissionDate.doctorOrder.diet:dietcode,dietdesc',
            'patient.admissionDate.dischargeOrder',
            'patient.admissionDate.billStat',
            'patient.admissionDate.bmi:enccode,vsweight,vsheight',
            // adding chargeslip makes the response to slow
            // 'patient.admissionDate.chargeSlip',
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
            ->paginate(15);

        dd($patients);

        return Inertia::render('Ward/Patients/Index', [
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
