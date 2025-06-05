<?php

namespace App\Http\Controllers\Wards\Inventory;


use App\Http\Controllers\Controller;
use App\Models\FundSource;
use App\Models\Item;
use App\Models\LocationStockBalanceDateLogs;
use App\Models\TypeOfCharge;
use App\Models\WardsStocks;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use App\Models\Sessions;
use App\Models\WardConsumptionTracker;
use App\Models\WardsStocksLogs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        dd(5);
        $searchString = $request->search;

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

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

        $items = DB::select(
            "SELECT
                item.cl2comb,
                item.cl2desc,
                item.uomcode,
                uom.uomdesc
            FROM
                hclass2 AS item
           JOIN huom AS uom
                ON uom.uomcode = item.uomcode
            WHERE
                (item.catID = 1
                AND item.uomcode != 'box'
                AND (item.itemcode NOT LIKE 'MSMG-%' OR item.itemcode IS NULL))
            ORDER BY
                item.cl2desc ASC;"
        );


        $currentWardStocks = DB::select(
            "SELECT stock.[from], stock.id, stock.cl2comb, item.cl2desc, stock.quantity, stock.average, stock.is_consumable, stock.expiration_date
                FROM csrw_wards_stocks stock
                JOIN hclass2 item ON stock.cl2comb = item.cl2comb
                -- LEFT JOIN huom uom ON stock.uomcode = uom.uomcode
                LEFT JOIN csrw_request_stocks rs ON rs.id = stock.request_stocks_id
                WHERE stock.location = ?
                AND stock.quantity > 0
                AND (rs.id IS NULL OR rs.status = 'RECEIVED');",
            [$authCode] // Duplicate the parameter
        );
        // dd($currentWardStocks);

        $latestDateLog = LocationStockBalanceDateLogs::where('wardcode', $authCode)
            ->latest('created_at')->first();
        $canTransact = null;
        if ($latestDateLog == null) {
            $canTransact = false;
        } else if ($latestDateLog != null && $latestDateLog->end_bal_created_at != null) {
            $canTransact = false;
        } else {
            $canTransact = true;
        }
        // dd($canTransact);

        return Inertia::render('Wards/Inventory/Index', [
            'items' => $items,
            'currentWardStocks' => $currentWardStocks,
            'canTransact' => $canTransact,
        ]);
    }
}
