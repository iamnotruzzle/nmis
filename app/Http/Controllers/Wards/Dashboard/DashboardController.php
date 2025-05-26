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
        $today = Carbon::today()->toDateString();

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

        #region top items
        // // cache version (refresh every 5 mins)
        $topItems = Cache::remember("charges_{$authCode}", 300, function () use ($authCode) {
            return DB::table('csrw_patient_charge_logs as logs')
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
        });
        // Prepare for Chart
        $topItems_labels = $topItems->pluck('description');
        $topItems_dataQty = $topItems->pluck('total_qty');
        $topItems_dataAmount = $topItems->pluck('total_amount');
        #endregion

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

        #region diagnosis
        if ($enctype !== 'ER' || $enctype ==  null) {
            $diagnosis = DB::select(
                "SELECT
                    hsubcateg.subcatdesc,
                    total = COUNT(hsubcateg.subcatdesc),
                    hsubcateg.diagsubcat,

                    -- 1 below
                    Male_1   = SUM(CASE WHEN hadmlog.patage < 1 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                    FeMale_1 = SUM(CASE WHEN hadmlog.patage < 1 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                    -- 1 to 4
                    Male_2   = SUM(CASE WHEN hadmlog.patage BETWEEN 1 AND 4 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                    FeMale_2 = SUM(CASE WHEN hadmlog.patage BETWEEN 1 AND 4 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                    -- 5 to 9
                    Male_3   = SUM(CASE WHEN hadmlog.patage BETWEEN 5 AND 9 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                    FeMale_3 = SUM(CASE WHEN hadmlog.patage BETWEEN 5 AND 9 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                    -- 10 to 14
                    Male_4   = SUM(CASE WHEN hadmlog.patage BETWEEN 10 AND 14 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                    FeMale_4 = SUM(CASE WHEN hadmlog.patage BETWEEN 10 AND 14 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                    -- 15 to 19
                    Male_5   = SUM(CASE WHEN hadmlog.patage BETWEEN 15 AND 19 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                    FeMale_5 = SUM(CASE WHEN hadmlog.patage BETWEEN 15 AND 19 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                    -- 20 to 44
                    Male_6   = SUM(CASE WHEN hadmlog.patage BETWEEN 20 AND 44 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                    FeMale_6 = SUM(CASE WHEN hadmlog.patage BETWEEN 20 AND 44 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                    -- 45 to 64
                    Male_7   = SUM(CASE WHEN hadmlog.patage BETWEEN 45 AND 64 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                    FeMale_7 = SUM(CASE WHEN hadmlog.patage BETWEEN 45 AND 64 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                    -- >= 65
                    Male_8   = SUM(CASE WHEN hadmlog.patage >= 65 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                    FeMale_8 = SUM(CASE WHEN hadmlog.patage >= 65 AND hperson.patsex = 'F' THEN 1 ELSE 0 END)

                FROM hadmlog
                JOIN hencdiag ON hadmlog.enccode = hencdiag.enccode
                JOIN hperson ON hadmlog.hpercode = hperson.hpercode
                JOIN hdiag ON hencdiag.diagcode = hdiag.diagcode
                JOIN hsubcateg ON hdiag.diagsubcat = hsubcateg.diagsubcat

                WHERE
                    hencdiag.primediag = 'Y'
                    AND hencdiag.tdcode = 'FINDX'
                    AND hadmlog.admdate BETWEEN '2025-04-01' AND DATEADD(DAY, 1, '2025-05-26')

                GROUP BY hsubcateg.subcatdesc, hsubcateg.diagsubcat
                ORDER BY total DESC;"
            );
            $diagnosisData = collect($diagnosis)->map(function ($item) {
                return [
                    'name' => $item->subcatdesc,
                    'value' => $item->total,
                    // Optional: add tooltip details if needed
                    'tooltip' => [
                        'formatter' => "{$item->subcatdesc}<br />Total: {$item->total}"
                    ],
                    // Optional: add children for age group breakdown
                    'children' => [
                        ['name' => 'M <1', 'value' => $item->Male_1],
                        ['name' => 'F <1', 'value' => $item->FeMale_1],
                        ['name' => 'M 1-4', 'value' => $item->Male_2],
                        ['name' => 'F 1-4', 'value' => $item->FeMale_2],
                        ['name' => 'M 5-9', 'value' => $item->Male_3],
                        ['name' => 'F 5-9', 'value' => $item->FeMale_3],
                        ['name' => 'M 10-14', 'value' => $item->Male_4],
                        ['name' => 'F 10-14', 'value' => $item->FeMale_4],
                        ['name' => 'M 15-19', 'value' => $item->Male_5],
                        ['name' => 'F 15-19', 'value' => $item->FeMale_5],
                        ['name' => 'M 20-44', 'value' => $item->Male_6],
                        ['name' => 'F 20-44', 'value' => $item->FeMale_6],
                        ['name' => 'M 45-64', 'value' => $item->Male_7],
                        ['name' => 'F 45-64', 'value' => $item->FeMale_7],
                        ['name' => 'M 65+', 'value' => $item->Male_8],
                        ['name' => 'F 65+', 'value' => $item->FeMale_8],
                    ]
                ];
            });
        } else if ($enctype == 'ER') {
            $diagnosis = DB::select(
                "SELECT
                hsubcateg.subcatdesc,
                total = COUNT(hsubcateg.subcatdesc),
                hsubcateg.diagsubcat,

                -- 1 below
                Male_1   = SUM(CASE WHEN herlog.patage < 1 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                FeMale_1 = SUM(CASE WHEN herlog.patage < 1 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                -- 1 to 4
                Male_2   = SUM(CASE WHEN herlog.patage BETWEEN 1 AND 4 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                FeMale_2 = SUM(CASE WHEN herlog.patage BETWEEN 1 AND 4 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                -- 5 to 9
                Male_3   = SUM(CASE WHEN herlog.patage BETWEEN 5 AND 9 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                FeMale_3 = SUM(CASE WHEN herlog.patage BETWEEN 5 AND 9 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                -- 10 to 14
                Male_4   = SUM(CASE WHEN herlog.patage BETWEEN 10 AND 14 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                FeMale_4 = SUM(CASE WHEN herlog.patage BETWEEN 10 AND 14 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                -- 15 to 19
                Male_5   = SUM(CASE WHEN herlog.patage BETWEEN 15 AND 19 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                FeMale_5 = SUM(CASE WHEN herlog.patage BETWEEN 15 AND 19 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                -- 20 to 44
                Male_6   = SUM(CASE WHEN herlog.patage BETWEEN 20 AND 44 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                FeMale_6 = SUM(CASE WHEN herlog.patage BETWEEN 20 AND 44 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                -- 45 to 64
                Male_7   = SUM(CASE WHEN herlog.patage BETWEEN 45 AND 64 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                FeMale_7 = SUM(CASE WHEN herlog.patage BETWEEN 45 AND 64 AND hperson.patsex = 'F' THEN 1 ELSE 0 END),

                -- >= 65
                Male_8   = SUM(CASE WHEN herlog.patage >= 65 AND hperson.patsex = 'M' THEN 1 ELSE 0 END),
                FeMale_8 = SUM(CASE WHEN herlog.patage >= 65 AND hperson.patsex = 'F' THEN 1 ELSE 0 END)

                FROM herlog
                JOIN hencdiag ON herlog.enccode = hencdiag.enccode
                --LEFT JOIN hpatacct ON herlog.enccode = hpatacct.enccode
                JOIN hperson ON herlog.hpercode = hperson.hpercode
                JOIN hdiag ON hencdiag.diagcode = hdiag.diagcode
                JOIN hsubcateg ON hdiag.diagsubcat = hsubcateg.diagsubcat

                WHERE
                    hencdiag.primediag = 'Y'
                    AND hencdiag.tdcode = 'FINDX'
                    AND herlog.erdate BETWEEN '2025-04-01' AND DATEADD(DAY, 1, '2025-05-26')
                    AND herlog.ercase = 'Y'

                GROUP BY hsubcateg.subcatdesc, hsubcateg.diagsubcat
                ORDER BY total DESC"
            );
            $diagnosisData = collect($diagnosis)->map(function ($item) {
                return [
                    'name' => $item->subcatdesc,
                    'value' => $item->total,
                    // Optional: add tooltip details if needed
                    'tooltip' => [
                        'formatter' => "{$item->subcatdesc}<br />Total: {$item->total}"
                    ],
                    // Optional: add children for age group breakdown
                    'children' => [
                        ['name' => 'M <1', 'value' => $item->Male_1],
                        ['name' => 'F <1', 'value' => $item->FeMale_1],
                        ['name' => 'M 1-4', 'value' => $item->Male_2],
                        ['name' => 'F 1-4', 'value' => $item->FeMale_2],
                        ['name' => 'M 5-9', 'value' => $item->Male_3],
                        ['name' => 'F 5-9', 'value' => $item->FeMale_3],
                        ['name' => 'M 10-14', 'value' => $item->Male_4],
                        ['name' => 'F 10-14', 'value' => $item->FeMale_4],
                        ['name' => 'M 15-19', 'value' => $item->Male_5],
                        ['name' => 'F 15-19', 'value' => $item->FeMale_5],
                        ['name' => 'M 20-44', 'value' => $item->Male_6],
                        ['name' => 'F 20-44', 'value' => $item->FeMale_6],
                        ['name' => 'M 45-64', 'value' => $item->Male_7],
                        ['name' => 'F 45-64', 'value' => $item->FeMale_7],
                        ['name' => 'M 65+', 'value' => $item->Male_8],
                        ['name' => 'F 65+', 'value' => $item->FeMale_8],
                    ]
                ];
            });
        }
        #endregion

        return Inertia::render('Wards/Dashboard/Index', [
            'patient_charges_total' => $patient_charges_total,
            'low_stock_items' => $low_stock_items,
            'ready_to_receive' => $ready_to_receive,
            'expiring_soon' => $expiring_soon,
            'latest_endorsement' => $latest_endorsement,
            'chargeChartData' => $chargeChartData,
            'topItems' => $topItems,
            'topItems_labels' => $topItems_labels,
            'topItems_dataQty' => $topItems_dataQty,
            'topItems_dataAmount' => $topItems_dataAmount,
            'lastMonthTotal' => $lastMonthTotal,
            'currentMonthTotal' => $currentMonthTotal,
            // 'diagnosis' => $diagnosis,
            'diagnosisData' => $diagnosisData,
        ]);
    }

    public function addDashboardData()
    {
        #region top items
        $topItems = DB::table('csrw_patient_charge_logs as logs')
            ->join('hclass2 as item', 'item.cl2comb', '=', 'logs.itemcode')
            ->select(
                'logs.itemcode',
                'item.cl2desc as description',
                DB::raw('SUM(logs.quantity) as total_qty'),
                DB::raw('SUM(logs.price_total) as total_amount')
            )
            ->where('logs.pcchrgdte', '>=', now()->subDays(3))
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

        #region current and last month total charge
        $previousMonth = DB::select(
            "SELECT SUM(price_total) AS last_month_total
                FROM csrw_patient_charge_logs
                WHERE MONTH(pcchrgdte) = MONTH(DATEADD(MONTH, -1, GETDATE()))
                AND YEAR(pcchrgdte) = YEAR(DATEADD(MONTH, -1, GETDATE()))
                AND entry_at = ?;",
            [$authCode]
        );
        $currentMonth = DB::select(
            "SELECT SUM(price_total) AS current_month_total
                FROM csrw_patient_charge_logs
                WHERE MONTH(pcchrgdte) = MONTH(GETDATE())
                AND YEAR(pcchrgdte) = YEAR(GETDATE())
                AND entry_at = ?",
            [$authCode]
        );
        $lastMonthTotal = $previousMonth[0]->last_month_total ?? 0;
        $currentMonthTotal = $currentMonth[0]->current_month_total ?? 0;
        // dd($lastMonthTotal);
        #endregion
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
