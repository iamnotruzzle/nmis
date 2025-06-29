<?php

namespace App\Http\Controllers\Wards\Patients;

use App\Http\Controllers\Controller;
use App\Models\PatientRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Sessions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class WardPatientsController extends Controller
{
    public function index(Request $request)
    {
        // Retrieve input parameters from the request
        $search = $request->search;
        $hpercode = $request->hpercode;
        $patfirst = $request->patfirst;
        $patlast = $request->patlast;

        #region auth ward code and ward location type
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
        $authCode = $authWardcode[0]->wardcode;

        $locationType_query = DB::select("SELECT enctype FROM hward WHERE wardcode = ?;", [$authCode]);
        // $locationType_cached = Cache::get($enctype);
        $enctype = !empty($locationType_query) ? $locationType_query[0]->enctype : null;
        // Retrieve the location type from cache again in case it was just set
        #endregion

        // Define cache keys for patient data and latest update timestamp
        $cacheKeyPatients = 'c_patients_' . $authCode;
        $cacheKeyLatestUpdate = 'latest_update_' . $authCode;

        // Attempt to retrieve cached patient data
        $cachedPatients = Cache::get($cacheKeyPatients);

        // If location type is null, fetch the latest admission date for the ward
        // locationType_cached
        if ($enctype === null) {
            $latestAdmDate = DB::select(
                "SELECT MAX(admdate) as admdate FROM hadmlog
                    RIGHT JOIN hospital.dbo.hpatroom pat_room ON hadmlog.enccode = pat_room.enccode
                    WHERE pat_room.patrmstat = 'A'
                    AND pat_room.wardcode = ?
                    AND hadmlog.admstat = 'A';",
                [$authCode]
            );

            // Extract the latest admission date from query result
            $latestAdmDate = $latestAdmDate[0]->admdate ?? null;

            // Retrieve the cached latest update timestamp
            $cachedAdmDate = Cache::get($cacheKeyLatestUpdate);

            // If the latest admission date has changed, fetch patient data and update the cache
            if (!$cachedAdmDate || $latestAdmDate !== $cachedAdmDate) {
                $fetchedPatients = DB::select(
                    "SELECT enctr.enccode, adm.admdate, enctr.hpercode, pt.patfirst, pt.patmiddle, pt.patlast, pt.patsuffix,
                        (SELECT TOP 1 vsweight FROM hvsothr WHERE othrstat = 'A' AND enccode = adm.enccode ORDER BY othrdte DESC) AS kg,
                        (SELECT TOP 1 vsheight FROM hvsothr WHERE othrstat = 'A' AND enccode = adm.enccode ORDER BY othrdte DESC) AS cm,
                        room.rmname, bed.bdname, ward.wardname,
                        adm.licno,
                        hpersonal.lastname, hpersonal.firstname, hpersonal.empsuffix,
                        ha.billstat AS bill_stat,
                        (SELECT TOP 1 orcode FROM hdocord WHERE enccode = enctr.enccode ORDER BY dodate DESC) AS is_for_discharge,
                        adm.tscode
                    FROM hospital.dbo.henctr enctr
                        RIGHT JOIN hospital.dbo.hadmlog adm ON enctr.enccode = adm.enccode
                        RIGHT JOIN hospital.dbo.hpatroom pat_room ON enctr.enccode = pat_room.enccode
                        RIGHT JOIN hospital.dbo.hroom room ON pat_room.rmintkey = room.rmintkey
                        RIGHT JOIN hospital.dbo.hbed bed ON bed.bdintkey = pat_room.bdintkey
                        RIGHT JOIN hospital.dbo.hward ward ON pat_room.wardcode = ward.wardcode
                        RIGHT JOIN hospital.dbo.hperson pt ON enctr.hpercode = pt.hpercode
                        LEFT JOIN hprovider ON hprovider.licno = adm.licno
                        LEFT JOIN hpersonal ON hpersonal.employeeid = hprovider.employeeid
                        LEFT JOIN hospital.dbo.hactrack ha ON adm.enccode = ha.enccode
                    WHERE (enctr.toecode = 'ADM' OR enctr.toecode = 'OPDAD' OR enctr.toecode = 'ERADM')
                    AND pat_room.wardcode = ?
                    AND pat_room.patrmstat = 'A'
                    AND adm.admstat = 'A'
                    ORDER BY pt.patlast ASC",
                    [$authCode]
                );

                // Update cache with new patient data and latest admission date
                // Cache::forever($cacheKeyLatestUpdate, $latestAdmDate);
                // Cache::forever($cacheKeyPatients, $fetchedPatients);
                // Store patient data and latest admission date in cache for 30 minutes
                Cache::put($cacheKeyLatestUpdate, $latestAdmDate, now()->addMinutes(60));
                Cache::put($cacheKeyPatients, $fetchedPatients, now()->addMinutes(60));
                $cachedPatients = $fetchedPatients;
            } else {
                // Retrieve patient data from cache if admission date has not changed
                $cachedPatients = Cache::get($cacheKeyPatients);
            }

            // Return the view with the cached or fetched patient data
            return Inertia::render('Wards/Patients/Index', [
                'patients' => $cachedPatients
            ]);
        } else if ($enctype == 'OPD') {

            if ($authCode == 'OB' || $authCode == 'GYNE') {
                $patients_query = DB::SELECT(
                    "SELECT
                        hopdlog.enccode,
                        hopdlog.hpercode,
                        hopdlog.opddate,
                        hopdlog.licno,
                        hpersonal.lastname,
                        hpersonal.firstname,
                        hpersonal.empsuffix,
                        hperson.patlast,
                        hperson.patfirst,
                        hperson.patmiddle,
                        htypser.tsdesc,
                        hopdlog.opddtedis,
                        hopdlog.opdstat,
                        hdisposition.dispdesc,
                        hopdlog.tscode

                    FROM hopdlog WITH (NOLOCK)

                    INNER JOIN hperson ON hperson.hpercode = hopdlog.hpercode
                    INNER JOIN htypser ON htypser.tscode = hopdlog.tscode
                    LEFT JOIN hdisposition ON hdisposition.dispcode = hopdlog.opddisp
                    LEFT JOIN hprovider ON hprovider.licno = hopdlog.licno
                    LEFT JOIN hpersonal ON hpersonal.employeeid = hprovider.employeeid

                    WHERE hopdlog.tscode IN ('OB', 'GYNE')
                    AND hopdlog.opddate BETWEEN CAST(GETDATE() AS DATE) AND DATEADD(DAY, 1, CAST(GETDATE() AS DATE)) -- prod
                    -- AND hopdlog.opddate BETWEEN CAST('2022-11-30' AS DATE) AND DATEADD(DAY, 1, CAST('2022-12-01' AS DATE)) -- test

                    AND hopdlog.licno IS NOT NULL

                    ORDER BY hopdlog.opddate DESC;",
                    // [$authWardCode_cached]
                );
            } else if ($authCode == 'FAMED') {
                $patients_query = DB::SELECT(
                    "SELECT
                        hopdlog.enccode,
                        hopdlog.hpercode,
                        hopdlog.opddate,
                        hopdlog.licno,
                        hpersonal.lastname,
                        hpersonal.firstname,
                        hpersonal.empsuffix,
                        hperson.patlast,
                        hperson.patfirst,
                        hperson.patmiddle,
                        htypser.tsdesc,
                        hopdlog.opddtedis,
                        hopdlog.opdstat,
                        hdisposition.dispdesc,
                        hopdlog.tscode

                    FROM hopdlog WITH (NOLOCK)

                    INNER JOIN hperson ON hperson.hpercode = hopdlog.hpercode
                    INNER JOIN htypser ON htypser.tscode = hopdlog.tscode
                    LEFT JOIN hdisposition ON hdisposition.dispcode = hopdlog.opddisp
                    LEFT JOIN hprovider ON hprovider.licno = hopdlog.licno
                    LEFT JOIN hpersonal ON hpersonal.employeeid = hprovider.employeeid

                    WHERE hopdlog.tscode LIKE 'FAM%'
                    AND hopdlog.opddate BETWEEN CAST(GETDATE() AS DATE) AND DATEADD(DAY, 1, CAST(GETDATE() AS DATE)) -- prod
                    -- AND hopdlog.opddate BETWEEN CAST('2022-11-30' AS DATE) AND DATEADD(DAY, 1, CAST('2022-12-01' AS DATE)) -- test

                    AND hopdlog.licno IS NOT NULL

                    ORDER BY hopdlog.opddate ASC;"
                );
                // dd($patients_query);
            } else {
                $patients_query = DB::SELECT(
                    "SELECT hopdlog.enccode, hopdlog.hpercode, hopdlog.opddate, hopdlog.licno,
                    hpersonal.lastname, hpersonal.firstname, hpersonal.empsuffix,
                    hperson.patlast, hperson.patfirst, hperson.patmiddle,
                    htypser.tsdesc, hopdlog.opddtedis, hopdlog.opdstat, hdisposition.dispdesc, hopdlog.tscode

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

                    AND hopdlog.licno IS NOT NULL
                    ORDER BY hopdlog.opddate desc;",
                    [$authCode]
                );
            }


            return Inertia::render('Wards/Patients/OPD/Index', [
                'patients' => $patients_query,
                'currWard' => $authCode,
            ]);
        } else if ($enctype == 'ER') {
            $latestERDateMod = DB::select(
                "SELECT MAX(datemod) AS datemod
                    FROM herlog
                    WHERE herlog.erdate BETWEEN DATEADD(HOUR, -12, GETDATE()) AND GETDATE();"
            );

            // Extract the latest datemod from query result
            $latestERDateMod = $latestERDateMod[0]->datemod ?? null;

            // Retrieve the cached latest update timestamp
            $cachedERDateMod = Cache::get($cacheKeyLatestUpdate);

            // If the latest datemod has changed, fetch patient data and update the cache
            if (!$cachedERDateMod || $latestERDateMod !== $cachedERDateMod) {
                ////old
                // $fetchedPatients = DB::SELECT(
                //     "SELECT
                //         herlog.enccode,
                //         herlog.hpercode,
                //         herlog.erdate,
                //         herlog.licno,
                //         hpersonal.lastname,
                //         hpersonal.firstname,
                //         hpersonal.empsuffix,
                //         hperson.patlast,
                //         hperson.patfirst,
                //         hperson.patmiddle,
                //         htypser.tsdesc,
                //         herlog.erdate,
                //         herlog.erstat,
                //         herlog.dispcode,
                //         henctr.toecode,
                //         herlog.datemod,
                //         herlog.tscode,
                //         -- Custom Bill Status Column
                //         (SELECT TOP 1 'BILLED'
                //         FROM csrw_patient_charge_logs
                //         WHERE csrw_patient_charge_logs.enccode = herlog.enccode
                //         AND csrw_patient_charge_logs.entry_at = 'ER') AS bill_status

                //     FROM herlog WITH (NOLOCK)
                //     JOIN henctr ON henctr.enccode = herlog.enccode
                //     INNER JOIN hperson ON hperson.hpercode = herlog.hpercode
                //     INNER JOIN htypser ON htypser.tscode = herlog.tscode
                //     LEFT JOIN hdisposition ON hdisposition.dispcode = herlog.dispcode
                //     LEFT JOIN hprovider ON hprovider.licno = herlog.licno
                //     LEFT JOIN hpersonal ON hpersonal.employeeid = hprovider.employeeid

                //     --1 day filter
                //     -- WHERE
                //     --     herlog.erdate BETWEEN DATEADD(DAY, -1, CAST(GETDATE() AS DATE))
                //     --                     AND DATEADD(DAY, 1, CAST(GETDATE() AS DATE))

                //     -- 1 1/2 day filter
                //     -- WHERE
                //     --         herlog.erdate BETWEEN DATEADD(DAY, -1.5, CAST(GETDATE() AS DATETIME))
                //     --                         AND DATEADD(DAY, 1.5, CAST(GETDATE() AS DATETIME))

                //     -- 2 days filter
                //     WHERE  herlog.erdate BETWEEN DATEADD(DAY, -2, GETDATE())
                //             AND GETDATE()

                //     ORDER BY herlog.erdate DESC;"
                // );

                //// new and optimized
                $fetchedPatients = DB::SELECT(
                    "SELECT
                        herlog.enccode,
                        herlog.hpercode,
                        herlog.erdate,
                        herlog.licno,
                        hpersonal.lastname,
                        hpersonal.firstname,
                        hpersonal.empsuffix,
                        hperson.patlast,
                        hperson.patfirst,
                        hperson.patmiddle,
                        htypser.tsdesc,
                        herlog.erdate,
                        herlog.erstat,
                        herlog.dispcode,
                        henctr.toecode,
                        herlog.datemod,
                        herlog.tscode,
                        billing.bill_status
                        -- Custom Bill Status Column
                        -- (SELECT TOP 1 'BILLED'
                        -- FROM csrw_patient_charge_logs
                        -- WHERE csrw_patient_charge_logs.enccode = herlog.enccode
                        -- AND csrw_patient_charge_logs.entry_at = 'ER') AS bill_status

                    FROM herlog WITH (NOLOCK)
                    JOIN henctr ON henctr.enccode = herlog.enccode
                    INNER JOIN hperson ON hperson.hpercode = herlog.hpercode
                    INNER JOIN htypser ON htypser.tscode = herlog.tscode
                    LEFT JOIN hdisposition ON hdisposition.dispcode = herlog.dispcode
                    LEFT JOIN hprovider ON hprovider.licno = herlog.licno
                    LEFT JOIN hpersonal ON hpersonal.employeeid = hprovider.employeeid
                    LEFT JOIN (
                        SELECT DISTINCT enccode, 'BILLED' AS bill_status
                        FROM csrw_patient_charge_logs
                        WHERE entry_at = 'ER'
                    ) AS billing ON billing.enccode = herlog.enccode

                    --1 day filter
                    -- WHERE
                    --     herlog.erdate BETWEEN DATEADD(DAY, -1, CAST(GETDATE() AS DATE))
                    --                     AND DATEADD(DAY, 1, CAST(GETDATE() AS DATE))

                    -- 1 1/2 day filter
                    -- WHERE
                    --         herlog.erdate BETWEEN DATEADD(DAY, -1.5, CAST(GETDATE() AS DATETIME))
                    --                         AND DATEADD(DAY, 1.5, CAST(GETDATE() AS DATETIME))

                    -- 2 days filter
                    WHERE  herlog.erdate BETWEEN DATEADD(DAY, -2, GETDATE())
                            AND GETDATE()

                    ORDER BY herlog.erdate DESC;"
                );

                // Update cache with new patient data and latest dateMod date
                // Cache::forever($cacheKeyLatestUpdate, $latestERDateMod);
                // Cache::forever($cacheKeyPatients, $fetchedPatients);
                // Store patient data and latest admission date in cache for 30 minutes
                Cache::put($cacheKeyLatestUpdate, $latestERDateMod, now()->addMinutes(60));
                Cache::put($cacheKeyPatients, $fetchedPatients, now()->addMinutes(60));
                $cachedPatients = $fetchedPatients;
            } else {
                // Retrieve patient data from cache if dateMod date has not changed
                $cachedPatients = Cache::get($cacheKeyPatients);
            }

            return Inertia::render('Wards/Patients/ER/Index', [
                'patients' => $cachedPatients
            ]);
        } else if ($enctype == 'OR') {
            // $hpercode != null && $hpercode != '' ||
            if ($hpercode != null && $hpercode != '') {
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
                    SELECT TOP 1
                        enccode,
                        toecode,
                        hpercode,
                        patfirst,
                        patmiddle,
                        patlast,
                        patsuffix,
                        encdate
                    FROM RankedRecords
                    WHERE RowNum = 1
                    ORDER BY encdate DESC;",
                    [
                        $hpercode . '%'
                    ]
                );
            } else if (($patfirst != null && $patlast != null)) {
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
                        WHERE (hperson.patfirst LIKE ? AND hperson.patlast LIKE ?)
                        AND henctr.toecode IN ('ADM', 'OPD', 'ER')
                    )
                    SELECT TOP 1
                        enccode,
                        toecode,
                        hpercode,
                        patfirst,
                        patmiddle,
                        patlast,
                        patsuffix,
                        encdate
                    FROM RankedRecords
                    WHERE RowNum = 1
                    ORDER BY encdate DESC;",
                    [
                        '%' . $patlast . '%',
                        '%' . $patfirst . '%'
                    ]
                );
            } else {
                $encounters = [];
            }

            // dd($encounters);

            return Inertia::render('Wards/Patients/OR/Index', [
                'encounters' => $encounters
            ]);
        } else if ($enctype == 'PACU') {
            // $hpercode != null && $hpercode != '' ||
            if ($hpercode != null && $hpercode != '') {
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
                    SELECT TOP 1
                        enccode,
                        toecode,
                        hpercode,
                        patfirst,
                        patmiddle,
                        patlast,
                        patsuffix,
                        encdate
                    FROM RankedRecords
                    WHERE RowNum = 1
                    ORDER BY encdate DESC;",
                    [
                        $hpercode . '%'
                    ]
                );
            } else if (($patfirst != null && $patlast != null)) {
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
                        WHERE (hperson.patfirst LIKE ? AND hperson.patlast LIKE ?)
                        AND henctr.toecode IN ('ADM', 'OPD', 'ER')
                    )
                    SELECT TOP 1
                        enccode,
                        toecode,
                        hpercode,
                        patfirst,
                        patmiddle,
                        patlast,
                        patsuffix,
                        encdate
                    FROM RankedRecords
                    WHERE RowNum = 1
                    ORDER BY encdate DESC;",
                    [
                        '%' . $patlast . '%',
                        '%' . $patfirst . '%'
                    ]
                );
            } else {
                $encounters = [];
            }

            // dd($encounters);

            return Inertia::render('Wards/Patients/OR/Index', [
                'encounters' => $encounters
            ]);
        } else if ($enctype == 'PD') {
            // $hpercode != null && $hpercode != '' ||
            if ($hpercode != null && $hpercode != '') {
                $encounters = DB::SELECT(
                    "SELECT TOP 5
                            h.enccode,
                            h.toecode,
                            p.hpercode,
                            p.patfirst,
                            p.patmiddle,
                            p.patlast,
                            p.patsuffix,
                            h.encdate
                        FROM hperson p
                        JOIN henctr h ON h.hpercode = p.hpercode
                        WHERE p.hpercode LIKE ?
                        AND h.toecode NOT IN ('WALKN')
                        ORDER BY h.encdate DESC",
                    [$hpercode]
                );
            } else if (($patfirst != null && $patlast != null)) {
                $encounters = DB::SELECT(
                    "SELECT TOP 5
                            h.enccode,
                            h.toecode,
                            p.hpercode,
                            p.patfirst,
                            p.patmiddle,
                            p.patlast,
                            p.patsuffix,
                            h.encdate
                        FROM hperson p
                        JOIN henctr h ON h.hpercode = p.hpercode
                        WHERE p.patfirst LIKE ?
                        AND p.patlast LIKE ?
                        AND h.toecode NOT IN ('WALKN')
                        ORDER BY h.encdate DESC",
                    [
                        '%' . $patfirst . '%',
                        '%' . $patlast . '%'
                    ]
                );
            } else {
                $encounters = [];
            }

            // dd($encounters);

            return Inertia::render('Wards/Patients/Peritoneal/Index', [
                'encounters' => $encounters
            ]);
        } else if ($enctype == 'OBC') {
            // $hpercode != null && $hpercode != '' ||
            if ($hpercode != null && $hpercode != '') {
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
                    SELECT TOP 1
                        enccode,
                        toecode,
                        hpercode,
                        patfirst,
                        patmiddle,
                        patlast,
                        patsuffix,
                        encdate
                    FROM RankedRecords
                    WHERE RowNum = 1
                    ORDER BY encdate DESC;",
                    [
                        $hpercode . '%'
                    ]
                );
            } else if (($patfirst != null && $patlast != null)) {
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
                        WHERE (hperson.patfirst LIKE ? AND hperson.patlast LIKE ?)
                        AND henctr.toecode IN ('ADM', 'OPD', 'ER')
                    )
                    SELECT TOP 1
                        enccode,
                        toecode,
                        hpercode,
                        patfirst,
                        patmiddle,
                        patlast,
                        patsuffix,
                        encdate
                    FROM RankedRecords
                    WHERE RowNum = 1
                    ORDER BY encdate DESC;",
                    [
                        '%' . $patlast . '%',
                        '%' . $patfirst . '%'
                    ]
                );
            } else {
                $encounters = [];
            }

            // dd($encounters);

            return Inertia::render('Wards/Patients/Peritoneal/Index', [
                'encounters' => $encounters
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
