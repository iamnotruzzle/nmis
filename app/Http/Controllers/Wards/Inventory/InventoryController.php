<?php

namespace App\Http\Controllers\Wards\Inventory;


use App\Http\Controllers\Controller;
use App\Models\FundSource;
use App\Models\Item;
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
        $searchString = $request->search;

        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        // Retrieve cached values
        $authWardCode_cached = Cache::get('c_authWardCode_' . Auth::user()->employeeid);
        $wardCode = $authWardCode_cached;

        // available items only show if quantity_after == total_issued_qty
        // $items = DB::select(
        //     "SELECT
        //         item.cl2comb,
        //         item.cl2desc,
        //         item.uomcode,
        //         uom.uomdesc
        //     FROM
        //         hclass2 AS item
        //     FULL OUTER JOIN
        //         huom AS uom
        //         ON uom.uomcode = item.uomcode
        //     WHERE
        //         (item.catID = 1
        //         AND item.uomcode != 'box'
        //         AND (item.itemcode NOT LIKE 'MSMG-%' OR item.itemcode IS NULL))
        //     ORDER BY
        //         item.cl2desc ASC;"
        // );
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

        // $currentWardStocks = DB::select(
        //     "SELECT ws.*,
        //         idt.cl2comb, idt.cl2desc,
        //         uom.uomcode, uom.uomdesc
        //         FROM csrw_wards_stocks ws
        //         LEFT JOIN hclass2 idt ON ws.cl2comb = idt.cl2comb
        //         LEFT JOIN huom uom ON ws.uomcode = uom.uomcode
        //         INNER JOIN csrw_request_stocks rs ON rs.id = ws.request_stocks_id
        //         WHERE ws.location = ?
        //         AND ws.quantity != 0
        //         AND rs.status = 'RECEIVED'

        //         UNION ALL

        //         SELECT ws.*,
        //             idt.cl2comb, idt.cl2desc,
        //             uom.uomcode, uom.uomdesc
        //         FROM csrw_wards_stocks ws
        //         LEFT JOIN hclass2 idt ON ws.cl2comb = idt.cl2comb
        //         LEFT JOIN huom uom ON ws.uomcode = uom.uomcode
        //         WHERE ws.request_stocks_id IS NULL
        //         AND ws.location = ?
        //         AND ws.quantity != 0",
        //     [$wardCode, $wardCode] // Duplicate the parameter
        // );


        $currentWardStocks = DB::select(
            "SELECT stock.[from], stock.id, stock.cl2comb, item.cl2desc, stock.quantity, stock.average, stock.is_consumable, stock.expiration_date
                FROM csrw_wards_stocks stock
                JOIN hclass2 item ON stock.cl2comb = item.cl2comb
                -- LEFT JOIN huom uom ON stock.uomcode = uom.uomcode
                LEFT JOIN csrw_request_stocks rs ON rs.id = stock.request_stocks_id
                WHERE stock.location = ?
                AND stock.quantity > 0
                AND (rs.id IS NULL OR rs.status = 'RECEIVED');",
            [$wardCode] // Duplicate the parameter
        );
        // dd($currentWardStocks);

        return Inertia::render('Wards/Inventory/Index', [
            'items' => $items,
            'currentWardStocks' => $currentWardStocks,
        ]);
    }
}
