<?php

namespace App\Http\Controllers\LocationStockBalance;

use App\Http\Controllers\Controller;
use App\Models\CsrStocks;
use App\Models\LocationStockBalance;
use App\Rules\StockBalanceRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LocationStockBalanceController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;
        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        $currentStocks = null;

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        // OLD condition. this also add CSR stock balance
        // if ($authWardcode->wardcode == 'CSR') {
        //     $currentStocks = DB::select(
        //         "SELECT clsb_csr.cl2comb as clsb_cl2comb, hc.cl2comb as hc_cl2comb, hc.cl2desc
        //         FROM csrw_csr_stocks as csr
        //         JOIN hclass2 as hc on csr.cl2comb = hc.cl2comb
        //         RIGHT JOIN (
        //             SELECT id, cl2comb, ending_balance, beginning_balance
        //             FROM csrw_location_stock_balance
        //             WHERE location = 'CSR' AND created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
        //         ) AS clsb_csr ON csr.cl2comb = clsb_csr.cl2comb;"
        //     );
        // } else {
        //     $currentStocks =  DB::select(
        //         "SELECT ward.id, clsb_ward.cl2comb as clsb_cl2comb, hc.cl2comb as hc_cl2comb, hc.cl2desc
        //             FROM csrw_wards_stocks as ward
        //             JOIN hclass2 as hc on ward.cl2comb = hc.cl2comb
        //             LEFT JOIN (
        //                 SELECT id, cl2comb, ending_balance, beginning_balance
        //                 FROM csrw_location_stock_balance
        //                 WHERE location = '$authWardcode->wardcode' AND created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
        //             ) AS clsb_ward ON ward.cl2comb = clsb_ward.cl2comb
        //             WHERE [from] = 'CSR' AND location = '$authWardcode->wardcode';"
        //     );
        //     dd($currentStocks);
        // }


        $currentStocks =  DB::select(
            "SELECT ward.id,
                    ward.ris_no,
                    clsb_ward.cl2comb as clsb_cl2comb,
                    hc.cl2comb as hc_cl2comb,
                    hc.cl2desc
                FROM csrw_wards_stocks as ward
                JOIN hclass2 as hc on ward.cl2comb = hc.cl2comb
                LEFT JOIN (
                    SELECT id, cl2comb, ending_balance, beginning_balance
                    FROM csrw_location_stock_balance
                    WHERE location = '$authWardcode->wardcode'
                        AND created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
                ) AS clsb_ward ON ward.cl2comb = clsb_ward.cl2comb
                WHERE [from] = 'CSR'
                AND quantity > 0
                AND ward.location = '$authWardcode->wardcode'"
        );

        $locationStockBalance = LocationStockBalance::with(['item:cl2comb,cl2desc', 'entry_by', 'updated_by'])
            ->where('location', $authWardcode->wardcode)
            ->when(
                $request->from,
                function ($query, $value) use ($from) {
                    $query->whereDate('created_at', '>=', $from);
                }
            )
            ->when(
                $request->to,
                function ($query, $value) use ($to) {
                    $query->whereDate('created_at', '<=', $to);
                }
            )
            ->whereHas('item', function ($q) use ($searchString) {
                $q->where('cl2desc', 'LIKE', '%' . $searchString . '%');
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        // dd($currentStocks);

        // dd(count($hasBalance));
        // return Inertia::render('Balance/Index', [
        //     'currentStocks' => $currentStocks,
        //     'locationStockBalance' => $locationStockBalance,
        // ]);

        // maintenance page
        return Inertia::render('UnderMaintenancePage', [
            // 'reports' => $reports
        ]);
    }

    public function store(Request $request)
    {
        // Get current date and check if it's between the 12th and the end of the current month
        $currentDate = Carbon::now()->startOfDay(); // Ensure we're comparing dates only
        // Explicitly set startOfBalancePeriod to the 12th of the current month
        $startOfBalancePeriod = $currentDate->copy()->setDay(12)->startOfDay();
        // End of month with the last moment of the day
        $endOfBalancePeriod = $currentDate->copy()->endOfMonth()->endOfDay();

        // dd([
        //     'currentDate' => $currentDate->toDateTimeString(),
        //     'startOfBalancePeriod' => $startOfBalancePeriod->toDateTimeString(),
        //     'endOfBalancePeriod' => $endOfBalancePeriod->toDateTimeString(),
        // ]);

        if ($currentDate->greaterThanOrEqualTo($startOfBalancePeriod) && $currentDate->lessThanOrEqualTo($endOfBalancePeriod)) {
            // Check if there's a balance record for this location for the current month before the 12th
            $hasBalance = DB::select(
                "SELECT *
                    FROM csrw_location_stock_balance
                    WHERE location = '$request->location'
                    AND MONTH(created_at) = MONTH(GETDATE())
                    AND YEAR(created_at) = YEAR(GETDATE());"
            );
            // dd($hasBalance);

            // Retrieve current stocks
            // $currentStocks = DB::select(
            //     "SELECT * FROM csrw_wards_stocks
            //         WHERE location = '$request->location'
            //         AND [from] = 'CSR'
            //         AND quantity > 0;"
            // );
            $currentStocks = DB::select(
                // "SELECT location, cl2comb, sum(quantity) as quantity, ris_no FROM csrw_wards_stocks
                //     WHERE location = '4FSA'
                //     AND [from] = 'CSR'
                //     AND quantity > 0
                //     GROUP BY location, cl2comb, ris_no;"
                "SELECT ward.id, ward.location, ward.cl2comb, sum(ward.quantity) as quantity, ward.ris_no, price.id as price_id
                    FROM csrw_wards_stocks as ward
                    JOIN csrw_item_prices as price ON price.ris_no = ward.ris_no
                    WHERE ward.location = '$request->location'
                    -- AND ward.[from] = 'CSR'
                    AND ward.quantity > 0
                    GROUP BY ward.location, ward.cl2comb, price.price_per_unit, ward.ris_no, ward.id, price.id"
            );
            dd($currentStocks);

            // If no balance has been declared before the 12th, create the balance
            $dateTime = Carbon::now();
            if (count($hasBalance) == 0) {
                foreach ($currentStocks as $stock) {
                    LocationStockBalance::create([
                        'location' => $request->location,
                        'cl2comb' => $stock->cl2comb,
                        'ending_balance' => $stock->quantity,
                        'beginning_balance' => $stock->quantity,
                        'ris_no' => $stock->ris_no,
                        'price_id' => $stock->price_id,
                        'entry_by' => $request->entry_by,
                        'ward_stock_id' => $stock->id,
                        'end_bal_created_at' => $dateTime,
                        'beg_bal_created_at' => $dateTime,
                    ]);
                }

                return redirect()->back()->with('success', 'Balance set successfully.');
            } else {
                // dd('declared');
                throw ValidationException::withMessages([
                    'error' => 'Stock balance for this month already declared.',
                ]);
            }
        } else {
            // dd('Balance can only be set between the 12th and the last day of the month.');
            // Return error if date is outside the allowed range
            throw ValidationException::withMessages([
                'error' => 'Balance can only be set between the 12th and the last day of the month.',
            ]);
        }
    }

    public function update(LocationStockBalance $stockbal, Request $request)
    {
        $request->validate([
            'cl2comb' => 'required',
            'ending_balance' => 'required',
            'beginning_balance' => 'required',
        ]);

        $stockbal->update([
            'location' => $request->location,
            'cl2comb' => $request->cl2comb,
            'ending_balance' => $request->ending_balance,
            'beginning_balance' => $request->beginning_balance,
            'updated_by' => $request->entry_by,
        ]);

        // dd($lsb);

        return redirect()->back();
    }

    public function destroy(LocationStockBalance $stockbal)
    {
        $stockbal->delete();

        return redirect()->back();
    }
}
