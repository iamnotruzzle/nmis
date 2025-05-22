<?php

namespace App\Http\Controllers\Wards\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{

    public function index()
    {
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

        $result_patient_charges_total = DB::select(
            "SELECT SUM(price_total) AS total_charges
                FROM csrw_patient_charge_logs
                WHERE entry_at = ?
                AND CAST(created_at AS DATE) = CAST(GETDATE() AS DATE);",
            [$authCode]
        );
        $patient_charges_total = round($result_patient_charges_total[0]->total_charges, 2);

        $result_low_stock_items = DB::select(
            "SELECT COUNT(*) AS low_stock_items
                FROM csrw_wards_stock_level AS r
                JOIN (
                    SELECT cl2comb, SUM(quantity) AS total_quantity
                    FROM csrw_wards_stocks
                    WHERE location = ?
                    GROUP BY cl2comb
                ) AS w ON r.cl2comb = w.cl2comb
                WHERE w.total_quantity <= r.reorder_point",
            [$authCode]
        );
        $low_stock_items = (int)$result_low_stock_items[0]->low_stock_items;

        $result_to_received = DB::select(
            "SELECT COUNT(*) as total
                FROM csrw_request_stocks
                WHERE status = 'FILLED'
                AND location = ?",
            [$authCode]
        );
        $ready_to_received = (int)$result_to_received[0]->total;

        $result_expiring_soon = DB::select(
            "SELECT COUNT(*) AS expiring_soon_count
                FROM csrw_wards_stocks
                WHERE location = ?
                AND expiration_date IS NOT NULL
                AND expiration_date BETWEEN CAST(GETDATE() AS DATE) AND DATEADD(DAY, 30, GETDATE())
                AND quantity > 0;",
            [$authCode]
        );
        $expiring_soon = (int)$result_expiring_soon[0]->expiring_soon_count;

        return Inertia::render('Wards/Dashboard/Index', [
            'patient_charges_total' => $patient_charges_total,
            'low_stock_items' => $low_stock_items,
            'ready_to_received' => $ready_to_received,
            'expiring_soon' => $expiring_soon
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
