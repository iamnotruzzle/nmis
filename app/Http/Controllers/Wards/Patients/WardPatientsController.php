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

        // $patients = PatientRoom::with([
        //     // when selecting an eager load table,
        //     // don't forget to get the foreign key as well
        //     'patient:patfirst,patmiddle,patlast,patsuffix,hpercode',
        //     'ward:wardcode,wardname,wclcode,wardstat',
        //     'room:rmintkey,wardcode,rmcode,rmname,rmbed,rmstat',
        //     'bed:bdintkey,wardcode,rmintkey,bdcode,bdname,bdstat'
        // ])
        //     // this will filter patients that hasn't been discharge
        //     ->whereHas('patient.admission', function ($q) {
        //         $q->where('disdate', null);
        //     })
        //     ->whereHas('patient', function ($q) use ($searchString) {
        //         $q->where('patfirst', 'LIKE', '%' . $searchString . '%')
        //             ->orWhere('patmiddle', 'LIKE', '%' . $searchString . '%')
        //             ->orWhere('patlast', 'LIKE', '%' . $searchString . '%');
        //     })
        //     ->where('patrmstat', 'a')
        //     ->where('wardcode', $authWardcode->wardcode)
        //     // order by patient
        //     ->join('hperson', 'hpatroom.hpercode', '=', 'hperson.hpercode')->orderBy('hperson.patlast', 'ASC')->select('hpatroom.*')
        //     ->paginate(10);



        $patients = DB::select(
            "SELECT enctr.enccode, adm.admdate, enctr.hpercode, pt.patfirst, pt.patmiddle, pt.patlast, pt.patsuffix,
                    (SELECT TOP 1 vsweight FROM hvsothr WHERE othrstat = 'A' AND enccode = adm.enccode ORDER BY othrdte DESC) as kg,
                    (SELECT TOP 1 vsheight FROM hvsothr WHERE othrstat = 'A' AND enccode = adm.enccode ORDER BY othrdte DESC) as cm,
                    room.rmname, bed.bdname, ward.wardname,
                    adm.licno,
                    (SELECT lastname + ', ' + firstname + ' ' + middlename FROM hpersonal WHERE physician_license_licnof.employeeid = hpersonal.employeeid) as physician_licnof,
                    (SELECT lastname + ', ' + firstname + ' ' + middlename FROM hpersonal WHERE physician_license_licno2.employeeid = hpersonal.employeeid) as physician_licno2,
                    (SELECT lastname + ', ' + firstname + ' ' + middlename FROM hpersonal WHERE physician_license_licno3.employeeid = hpersonal.employeeid) as physician_licno3,
                    (SELECT lastname + ', ' + firstname + ' ' + middlename FROM hpersonal WHERE physician_license_licno4.employeeid = hpersonal.employeeid) as physician_licno4,
                    ha.billstat as bill_stat,
                    (SELECT TOP 1 orcode FROM hdocord WHERE enccode = enctr.enccode ORDER BY dodate DESC) is_for_discharge
                FROM hospital.dbo.henctr enctr
                    RIGHT JOIN hospital.dbo.hadmlog adm ON enctr.enccode = adm.enccode
                    RIGHT JOIN hospital.dbo.hpatroom pat_room ON enctr.enccode = pat_room.enccode
                    RIGHT JOIN hospital.dbo.hroom room ON pat_room.rmintkey = room.rmintkey
                    RIGHT JOIN hospital.dbo.hbed bed ON bed.bdintkey = pat_room.bdintkey
                    RIGHT JOIN hospital.dbo.hward ward ON pat_room.wardcode = ward.wardcode
                    RIGHT JOIN hospital.dbo.hperson pt ON enctr.hpercode = pt.hpercode
                    LEFT JOIN hospital.dbo.hprovider physician_license_licnof ON adm.licnof = physician_license_licnof.licno
                    LEFT JOIN hospital.dbo.hprovider physician_license_licno2 ON adm.licno2 = physician_license_licno2.licno
                    LEFT JOIN hospital.dbo.hprovider physician_license_licno3 ON adm.licno3 = physician_license_licno3.licno
                    LEFT JOIN hospital.dbo.hprovider physician_license_licno4 ON adm.licno4 = physician_license_licno4.licno
                    LEFT JOIN hospital.dbo.hactrack ha ON adm.enccode = ha.enccode
                WHERE (enctr.toecode = 'ADM' OR enctr.toecode = 'OPDAD' OR enctr.toecode = 'ERADM')
                AND pat_room.wardcode = ?
                AND pat_room.patrmstat = 'A'
                ORDER BY pt.patlast ASC;",
            [$authWardcode->wardcode]
        );

        //   [$authWardcode->wardcode]

        // dd($authWardcode->wardcode);
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
