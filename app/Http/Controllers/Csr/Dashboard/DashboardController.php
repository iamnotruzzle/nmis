<?php

namespace App\Http\Controllers\Csr\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\RequestStocks;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $completed_request_month = RequestStocks::where('status', 'RECEIVED')
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
        $completed_request_week = RequestStocks::where('status', 'RECEIVED')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $completed_request_today = RequestStocks::where('status', 'RECEIVED')
            ->where('created_at', Carbon::today())->count();

        $total_cost_month = DB::select(
            "SELECT ((SELECT SUM(approved_qty) FROM csrw_request_stocks_details WHERE cl2comb = rsd.cl2comb) * (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = rsd.cl2comb ORDER BY created_at DESC)) as total_cost
            FROM csrw_request_stocks_details as rsd
            JOIN csrw_item_prices as prices ON rsd.cl2comb = prices.cl2comb
            WHERE rsd.created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
            GROUP BY rsd.cl2comb"
        );
        $total_cost_week = DB::select(
            "SELECT ((SELECT SUM(approved_qty) FROM csrw_request_stocks_details WHERE cl2comb = rsd.cl2comb) * (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = rsd.cl2comb ORDER BY created_at DESC)) as total_cost
            FROM csrw_request_stocks_details as rsd
            JOIN csrw_item_prices as prices ON rsd.cl2comb = prices.cl2comb
            WHERE DateDiff(wk,getdate(),rsd.created_at) =  0
            GROUP BY rsd.cl2comb"
        );

        return Inertia::render('Csr/Dashboard/Index', [
            'completed_request_month' => $completed_request_month,
            'completed_request_week' => $completed_request_week,
            'completed_request_today' => $completed_request_today,
            'total_cost_month' => $total_cost_month,
            'total_cost_week' => $total_cost_week,
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
