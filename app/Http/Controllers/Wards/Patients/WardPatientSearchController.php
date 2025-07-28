<?php

namespace App\Http\Controllers\Wards\Patients;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WardPatientSearchController extends Controller
{
    public function index(Request $request)
    {
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
                            CASE
                                WHEN henctr.toecode = 'ADM' THEN adm.tscode
                                WHEN henctr.toecode = 'OPD' THEN opd.tscode
                                WHEN henctr.toecode = 'ER' THEN er.tscode
                                ELSE NULL
                            END AS tscode,
                            ROW_NUMBER() OVER (PARTITION BY henctr.toecode ORDER BY henctr.encdate DESC) AS RowNum
                        FROM hperson
                        JOIN henctr ON henctr.hpercode = hperson.hpercode
                        LEFT JOIN hadmlog AS adm ON adm.enccode = henctr.enccode
                        LEFT JOIN hopdlog AS opd ON opd.enccode = henctr.enccode
                        LEFT JOIN herlog AS er ON er.enccode = henctr.enccode
                        WHERE hperson.hpercode LIKE ?
                        AND henctr.toecode IN ('ADM', 'OPD', 'ER')
                    )
                    SELECT TOP 2
                        enccode,
                        toecode,
                        hpercode,
                        patfirst,
                        patmiddle,
                        patlast,
                        patsuffix,
                        encdate,
                        tscode
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
                            CASE
                                WHEN henctr.toecode = 'ADM' THEN adm.tscode
                                WHEN henctr.toecode = 'OPD' THEN opd.tscode
                                WHEN henctr.toecode = 'ER' THEN er.tscode
                                ELSE NULL
                            END AS tscode,
                            ROW_NUMBER() OVER (PARTITION BY henctr.toecode ORDER BY henctr.encdate DESC) AS RowNum
                        FROM hperson
                        JOIN henctr ON henctr.hpercode = hperson.hpercode
                        LEFT JOIN hadmlog AS adm ON adm.enccode = henctr.enccode
                        LEFT JOIN hopdlog AS opd ON opd.enccode = henctr.enccode
                        LEFT JOIN herlog AS er ON er.enccode = henctr.enccode
                        WHERE (hperson.patfirst LIKE ? AND hperson.patlast LIKE ?)
                        AND henctr.toecode IN ('ADM', 'OPD', 'ER')
                    )
                    SELECT TOP 2
                        enccode,
                        toecode,
                        hpercode,
                        patfirst,
                        patmiddle,
                        patlast,
                        patsuffix,
                        encdate,
                        tscode
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

        return Inertia::render('Wards/Patients/Search/Index', [
            'encounters' => $encounters
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
