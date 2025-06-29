<?php

namespace App\Http\Controllers\Wards\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sessions;
use Carbon\Carbon;
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
        $authCode = $authWardcode[0]->wardcode;
        $locationType_query = DB::select("SELECT enctype FROM hward WHERE wardcode = ?;", [$authCode]);
        $enctype = !empty($locationType_query) ? $locationType_query[0]->enctype : null;

        $result_low_stock_items = DB::select(
            "SELECT COUNT(*) AS low_stock_items
                FROM csrw_wards_stock_level AS r
                JOIN (
                    SELECT cl2comb, SUM(quantity) AS total_quantity
                    FROM csrw_wards_stocks
                    WHERE location = ?
                    GROUP BY cl2comb
                ) AS w ON r.cl2comb = w.cl2comb
                WHERE w.total_quantity <= r.reorder_point
                AND wardcode = ?",
            [$authCode, $authCode]
        );
        $low_stock_items = (int)$result_low_stock_items[0]->low_stock_items;

        $result_to_receive = DB::select(
            "SELECT COUNT(*) as total
                FROM csrw_request_stocks
                WHERE status = 'FILLED'
                AND location = ?",
            [$authCode]
        );
        $ready_to_receive = (int)$result_to_receive[0]->total;

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

        $latest_endorsement = DB::select(
            "SELECT person.firstname, person.lastname, d.description, d.tag, d.status, e.created_at
                FROM (
                    SELECT TOP 1 *
                    FROM csrw_wa_endorsements
                    WHERE wardcode = ? AND soft_delete IS NULL
                    ORDER BY created_at DESC
                ) AS e
                JOIN csrw_wa_endorsements_details AS d ON d.endorsement_id = e.id
                JOIN hpersonal as person ON person.employeeid = e.from_user;",
            [$authCode]
        );

        // #region charges
        // $charges = DB::table('csrw_patient_charge_logs')
        //     ->selectRaw("CONVERT(date, pcchrgdte) AS charge_date, SUM(price_total) AS total_charge_amount")
        //     ->where('pcchrgdte', '>=', now()->subDays(7))
        //     ->where('entry_at', $authCode)
        //     ->groupByRaw("CONVERT(date, pcchrgdte)")
        //     ->orderBy('charge_date')
        //     ->get();
        // // Format for Chart.js
        // $chargeChartData = [
        //     'labels' => $charges->pluck('charge_date')->map(fn($d) => Carbon::parse($d)->format('M d')),
        //     'datasets' => [[
        //         'label' => 'Daily Charges (₱)',
        //         'data' => $charges->pluck('total_charge_amount'),
        //         'borderColor' => 'rgb(75, 192, 192)',
        //         'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
        //         'fill' => true,
        //         'tension' => 0.3
        //     ]]
        // ];

        // // 👇 Extract today's charge total from the cached $charges
        // $result_patient_charges_total = $charges->firstWhere('charge_date', $today)?->total_charge_amount ?? 0;
        // $patient_charges_total = round($result_patient_charges_total, 2);
        // #endregion

        // #region top items
        // $topItems = Cache::remember("charges_{$authCode}", 300, function () use ($authCode) {
        //     return DB::table('csrw_patient_charge_logs as logs')
        //         ->join('hclass2 as item', 'item.cl2comb', '=', 'logs.itemcode')
        //         ->select(
        //             'logs.itemcode',
        //             'item.cl2desc as description',
        //             DB::raw('SUM(logs.quantity) as total_qty'),
        //             DB::raw('SUM(logs.price_total) as total_amount')
        //         )
        //         ->where('logs.pcchrgdte', '>=', now()->subDays(7))
        //         ->where('logs.entry_at', $authCode)
        //         ->groupBy('logs.itemcode', 'item.cl2desc')
        //         ->orderByDesc('total_qty')
        //         ->limit(10)
        //         ->get();
        // });
        // // Prepare for Chart
        // $topItems_labels = $topItems->pluck('description');
        // $topItems_dataQty = $topItems->pluck('total_qty');
        // $topItems_dataAmount = $topItems->pluck('total_amount');
        // #endregion

        // #region current and last month total charge
        // $previousMonth = DB::select(
        //     "SELECT price_total
        //         FROM csrw_patient_charge_logs
        //         WHERE MONTH(pcchrgdte) = MONTH(DATEADD(MONTH, -1, GETDATE()))
        //         AND YEAR(pcchrgdte) = YEAR(DATEADD(MONTH, -1, GETDATE()))
        //         AND entry_at = ?;",
        //     [$authCode]
        // );
        // $currentMonth = DB::select(
        //     "SELECT price_total
        //         FROM csrw_patient_charge_logs
        //         WHERE MONTH(pcchrgdte) = MONTH(GETDATE())
        //         AND YEAR(pcchrgdte) = YEAR(GETDATE())
        //         AND entry_at = ?",
        //     [$authCode]
        // );
        // $lastMonthTotal = array_sum(array_map(fn($row) => (float) $row->price_total, $previousMonth));
        // $currentMonthTotal = array_sum(array_map(fn($row) => (float) $row->price_total, $currentMonth));
        // #endregion


        return Inertia::render('Wards/Dashboard/Index', [
            'low_stock_items' => $low_stock_items,
            'ready_to_receive' => $ready_to_receive,
            'expiring_soon' => $expiring_soon,
            'latest_endorsement' => $latest_endorsement,

            // 'chargeChartData' => $chargeChartData,
            // 'patient_charges_total' => $patient_charges_total,

            // 'topItems' => $topItems,
            // 'topItems_labels' => $topItems_labels,
            // 'topItems_dataQty' => $topItems_dataQty,
            // 'topItems_dataAmount' => $topItems_dataAmount,
            // 'lastMonthTotal' => $lastMonthTotal,
            // 'currentMonthTotal' => $currentMonthTotal,
        ]);
    }

    public function topDiagnosesData(Request $request)
    {
        $from = $request->from;
        $to = $request->to;

        // Default to current month if not provided
        if (!$from || !$to) {
            $from = now()->startOfMonth()->toDateString();
            $to = now()->toDateString();
        }

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
        $authCode = $authWardcode[0]->wardcode;
        $locationType_query = DB::select("SELECT enctype FROM hward WHERE wardcode = ?;", [$authCode]);
        $enctype = !empty($locationType_query) ? $locationType_query[0]->enctype : null;

        if ($enctype == 'ER') {
            $topDiagnosis = DB::select(
                "SELECT
                    TOP 10
                    hsubcateg.subcatdesc as final_diagnosis,
                    COUNT(DISTINCT herlog.enccode) AS patient_count
                FROM herlog
                JOIN hencdiag ON herlog.enccode = hencdiag.enccode
                JOIN hperson ON herlog.hpercode = hperson.hpercode
                JOIN hdiag ON hencdiag.diagcode = hdiag.diagcode
                JOIN hsubcateg ON hdiag.diagsubcat = hsubcateg.diagsubcat
                WHERE
                    hencdiag.primediag = 'Y'
                    AND hencdiag.tdcode = 'FINDX'
                    -- AND herlog.erdate BETWEEN '2025-02-01' AND DATEADD(DAY, 1, '2025-05-26')
                    AND herlog.erdate BETWEEN ? AND DATEADD(DAY, 1, ?)
                    AND herlog.ercase = 'Y'
                GROUP BY hsubcateg.subcatdesc
                ORDER BY patient_count DESC;",
                [$from, $to]
            );
        } else if ($enctype == 'OPD') {
            $topDiagnosis = DB::select(
                "SELECT
                    TOP 10
                    hsubcateg.subcatdesc as final_diagnosis,
                    COUNT(DISTINCT hopdlog.enccode) AS patient_count
                FROM hopdlog
                JOIN hencdiag ON hopdlog.enccode = hencdiag.enccode
                JOIN hperson ON hopdlog.hpercode = hperson.hpercode
                JOIN hdiag ON hencdiag.diagcode = hdiag.diagcode
                JOIN hsubcateg ON hdiag.diagsubcat = hsubcateg.diagsubcat
                WHERE
                    hencdiag.primediag = 'Y'
                    AND hencdiag.tdcode = 'FINDX'
                    -- AND hopdlog.erdate BETWEEN '2025-02-01' AND DATEADD(DAY, 1, '2025-05-26')
                    AND hopdlog.erdate BETWEEN ? AND DATEADD(DAY, 1, ?)
                    AND hopdlog.ercase = 'Y'
                GROUP BY hsubcateg.subcatdesc
                ORDER BY patient_count DESC;",
                [$from, $to]
            );
        } else {
            $topDiagnosis = DB::select(
                "SELECT
                    TOP 10
                    hsubcateg.subcatdesc as final_diagnosis,
                    COUNT(DISTINCT hadmlog.enccode) AS patient_count
                FROM hadmlog
                JOIN hencdiag ON hadmlog.enccode = hencdiag.enccode
                JOIN hperson ON hadmlog.hpercode = hperson.hpercode
                JOIN hdiag ON hencdiag.diagcode = hdiag.diagcode
                JOIN hsubcateg ON hdiag.diagsubcat = hsubcateg.diagsubcat
                WHERE
                    hencdiag.primediag = 'Y'
                    AND hencdiag.tdcode = 'FINDX'
                    -- AND hadmlog.erdate BETWEEN '2025-02-01' AND DATEADD(DAY, 1, '2025-05-26')
                    AND hadmlog.admdate BETWEEN ? AND DATEADD(DAY, 1, ?)
                GROUP BY hsubcateg.subcatdesc
                ORDER BY patient_count DESC;",
                [$from, $to]
            );
        }

        return response()->json($topDiagnosis);
    }

    public function topTenItemsData(Request $request)
    {
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

        #region top items
        // value of topItems is cached for 5mins
        // $topItems = Cache::remember("charges_{$authCode}", 300, function () use ($authCode) {
        //     return DB::table('csrw_patient_charge_logs as logs')
        //         ->join('hclass2 as item', 'item.cl2comb', '=', 'logs.itemcode')
        //         ->select(
        //             'logs.itemcode',
        //             'item.cl2desc as description',
        //             DB::raw('SUM(logs.quantity) as total_qty'),
        //             DB::raw('SUM(logs.price_total) as total_amount')
        //         )
        //         ->where('logs.pcchrgdte', '>=', now()->subDays(7))
        //         ->where('logs.entry_at', $authCode)
        //         ->groupBy('logs.itemcode', 'item.cl2desc')
        //         ->orderByDesc('total_qty')
        //         ->limit(10)
        //         ->get();
        // });
        // regular query, no caching
        $topItems = DB::table('csrw_patient_charge_logs as logs')
            ->join('hclass2 as item', 'item.cl2comb', '=', 'logs.itemcode')
            ->select(
                'logs.itemcode',
                'item.cl2desc as description',
                DB::raw('SUM(logs.quantity) as total_qty'),
                DB::raw('SUM(logs.price_total) as total_amount')
            )
            ->where('logs.pcchrgdte', '>=', now()->subDays(7))
            ->where('logs.entry_at', $authCode)
            ->groupBy('logs.itemcode', 'item.cl2desc')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Prepare for Chart
        $topItems_labels = $topItems->pluck('description');
        $topItems_dataQty = $topItems->pluck('total_qty');
        $topItems_dataAmount = $topItems->pluck('total_amount');
        #endregion

        return response()->json([
            'topItems' => $topItems,
            'topItems_labels' => $topItems_labels,
            'topItems_dataQty' => $topItems_dataQty,
            'topItems_dataAmount' => $topItems_dataAmount,
        ]);
    }

    public function monthlyCharge(Request $request)
    {
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

        #region current and last month total charge
        $previousMonth = DB::select(
            "SELECT price_total
                FROM csrw_patient_charge_logs
                WHERE MONTH(pcchrgdte) = MONTH(DATEADD(MONTH, -1, GETDATE()))
                AND YEAR(pcchrgdte) = YEAR(DATEADD(MONTH, -1, GETDATE()))
                AND entry_at = ?;",
            [$authCode]
        );
        $currentMonth = DB::select(
            "SELECT price_total
                FROM csrw_patient_charge_logs
                WHERE MONTH(pcchrgdte) = MONTH(GETDATE())
                AND YEAR(pcchrgdte) = YEAR(GETDATE())
                AND entry_at = ?",
            [$authCode]
        );
        $lastMonthTotal = array_sum(array_map(fn($row) => (float) $row->price_total, $previousMonth));
        $currentMonthTotal = array_sum(array_map(fn($row) => (float) $row->price_total, $currentMonth));
        #endregion

        return response()->json([
            'lastMonthTotal' => $lastMonthTotal,
            'currentMonthTotal' => $currentMonthTotal,
        ]);
    }

    public function charges(Request $request)
    {
        $today = Carbon::today()->toDateString();

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

        #region charges
        $charges = DB::table('csrw_patient_charge_logs')
            ->selectRaw("CONVERT(date, pcchrgdte) AS charge_date, SUM(price_total) AS total_charge_amount")
            ->where('pcchrgdte', '>=', now()->subDays(7))
            ->where('entry_at', $authCode)
            ->groupByRaw("CONVERT(date, pcchrgdte)")
            ->orderBy('charge_date')
            ->get();
        // Format for Chart.js
        $chargeChartData = [
            'labels' => $charges->pluck('charge_date')->map(fn($d) => Carbon::parse($d)->format('M d')),
            'datasets' => [[
                'label' => 'Daily Charges (₱)',
                'data' => $charges->pluck('total_charge_amount'),
                'borderColor' => 'rgb(75, 192, 192)',
                'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                'fill' => true,
                'tension' => 0.3
            ]]
        ];

        // 👇 Extract today's charge total from the cached $charges
        $result_patient_charges_total = $charges->firstWhere('charge_date', $today)?->total_charge_amount ?? 0;
        $patient_charges_total = round($result_patient_charges_total, 2);
        #endregion

        return response()->json([
            'chargeChartData' => $chargeChartData,
            'patient_charges_total' => $patient_charges_total,
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
