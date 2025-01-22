<?php

namespace App\Http\Controllers\Wards\Patients;

use App\Http\Controllers\Controller;
use App\Models\PatientRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class WardPatientsController extends Controller
{
    public function index(Request $request)
    {
        // dd($request);

        $search = $request->search;

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
        // dd($authWardcode[0]->wardcode);

        $locationType = DB::select(
            "SELECT enctype FROM hward WHERE wardcode = ?;",
            [$authWardcode[0]->wardcode]
        );
        // dd($locationType[0]->enctype);

        if ($locationType[0]->enctype == 'OPD') {
            // dd('OPD');

            $patients = DB::SELECT(
                "SELECT hopdlog.enccode, hopdlog.hpercode, hopdlog.opddate, hopdlog.licno,
                hpersonal.lastname, hpersonal.firstname, hpersonal.empsuffix,
                hperson.patlast, hperson.patfirst, hperson.patmiddle,
                htypser.tsdesc, hopdlog.opddtedis, hopdlog.opdstat

                FROM hopdlog
                WITH (NOLOCK)

                INNER JOIN hperson ON hperson.hpercode = hopdlog.hpercode
                INNER JOIN htypser ON htypser.tscode = hopdlog.tscode
                LEFT JOIN hdisposition ON hdisposition.dispcode = hopdlog.opddisp
                LEFT JOIN hprovider ON hprovider.licno = hopdlog.licno
                LEFT JOIN hpersonal ON hpersonal.employeeid = hprovider.employeeid

                WHERE hopdlog.tscode = ? /* tscode here */
                AND hopdlog.opddate BETWEEN CAST(GETDATE() AS DATE) AND DATEADD(DAY, 1, CAST(GETDATE() AS DATE)) -- prod
                -- AND hopdlog.opddate BETWEEN CAST('2022-11-30' AS DATE) AND DATEADD(DAY, 1, CAST('2022-12-01' AS DATE)) -- test
                AND hopdlog.opdstat = 'A'
                AND hopdlog.licno IS NOT NULL
                ORDER BY hopdlog.opddate desc",
                [$authWardcode[0]->wardcode]
            );

            return Inertia::render('Wards/Patients/OPD/Index', [
                'patients' => $patients
            ]);
        } else if ($locationType[0]->enctype == 'ER') {
            // $pastSevenDays = Carbon::parse(now())->subDays(7)->format('Y-m-d H:i');
            // herlog.erdate > '$pastSevenDays' --prod: filter past 7 days including today

            $patients = DB::SELECT(
                // this query includes all patients of ER, including patients that was transferred from ER to WARD for admission.
                "SELECT herlog.enccode, herlog.hpercode, herlog.erdate, herlog.licno,
                    hpersonal.lastname, hpersonal.firstname, hpersonal.empsuffix,
                    hperson.patlast, hperson.patfirst, hperson.patmiddle,
                    htypser.tsdesc, herlog.erdtedis, herlog.erstat, herlog.dispcode, henctr.toecode

                    FROM herlog
                    WITH (NOLOCK)

                    JOIN henctr ON henctr.enccode = herlog.enccode
                    INNER JOIN hperson ON hperson.hpercode = herlog.hpercode
                    INNER JOIN htypser ON htypser.tscode = herlog.tscode
                    LEFT JOIN hdisposition ON hdisposition.dispcode = herlog.dispcode
                    LEFT JOIN hprovider ON hprovider.licno = herlog.licno
                    LEFT JOIN hpersonal ON hpersonal.employeeid = hprovider.employeeid

                    WHERE
                        -- herlog.erdate BETWEEN CAST('2022-06-01' AS DATE) AND DATEADD(DAY, 1, CAST('2022-06-02' AS DATE)) -- test 1 day
                        herlog.erdate BETWEEN CAST(GETDATE() AS DATE) AND DATEADD(DAY, 1, CAST(GETDATE() AS DATE)) -- prod
                    ORDER BY herlog.erdate desc"
            );

            return Inertia::render('Wards/Patients/ER/Index', [
                'patients' => $patients
            ]);
        } else if ($locationType[0]->enctype == 'OR') {
            $encounters = DB::SELECT(
                "WITH RankedRecords AS (
                    SELECT
                        henctr.enccode,
                        henctr.toecode,
                        hperson.hpercode,
                        hperson.patfirst,
                        hperson.patmiddle,
                        hperson.patlast,
                        hperson.patsuffix,
                        henctr.encdate,
                        ROW_NUMBER() OVER (PARTITION BY henctr.toecode ORDER BY henctr.encdate DESC) AS RowNum
                    FROM hperson
                    JOIN henctr ON henctr.hpercode = hperson.hpercode
                    WHERE hperson.hpercode LIKE ?
                    AND henctr.toecode IN ('ADM', 'OPD', 'ER')
                )
                SELECT
                    enccode,
                    toecode,
                    patfirst,
                    patmiddle,
                    patlast,
                    patsuffix,
                    encdate
                FROM RankedRecords
                WHERE RowNum = 1
                ORDER BY encdate DESC;",
                [$request->search]
            );

            return Inertia::render('Wards/Patients/OR/Index', [
                'encounters' => $encounters
            ]);
        } else {
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
                    AND adm.admstat = 'A'
                    ORDER BY pt.patlast ASC;",
                // [$authWardcode->wardcode]
                [$authWardcode[0]->wardcode]
            );

            return Inertia::render('Wards/Patients/Index', [
                'patients' => $patients
            ]);
        }
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
