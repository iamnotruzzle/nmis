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
        // completed requests
        $completed_requests_month = RequestStocks::where('status', 'RECEIVED')
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
        // pending requests
        $pending_requests_month = RequestStocks::where('status', 'REQUESTED')
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();

        // total cost of issued items
        $total_issued_cost_month = DB::select(
            "SELECT (SUM(rsd.approved_qty) * (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = rsd.cl2comb ORDER BY created_at DESC)) as total_cost
            FROM csrw_request_stocks_details  as rsd
            WHERE rsd.created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
            GROUP BY rsd.cl2comb;"
        );

        // most requested items
        $most_requested_month = DB::select(
            "SELECT TOP 5 hc.cl2desc as item,  SUM(rsd.requested_qty) as quantity
            FROM csrw_request_stocks_details as rsd
            JOIN hclass2 as hc ON rsd.cl2comb = hc.cl2comb
            WHERE rsd.created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
            GROUP BY rsd.cl2comb, hc.cl2desc, rsd.requested_qty
            ORDER BY quantity DESC;"
        );


        return Inertia::render('Csr/Dashboard/Index', [
            'completed_requests_month' => $completed_requests_month,
            'pending_requests_month' => $pending_requests_month,
            'total_issued_cost_month' => $total_issued_cost_month,
            'most_requested_month' => $most_requested_month,
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
