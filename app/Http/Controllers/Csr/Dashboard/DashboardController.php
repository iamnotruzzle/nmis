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
        $pending_requests = RequestStocks::where('status', 'PENDING')->count();

        $completed_requests_month = RequestStocks::where('status', 'RECEIVED')
            ->whereBetween('received_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
        // pending requests
        $pending_requests_month = RequestStocks::where('status', 'REQUESTED')
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();

        // total cost of issued items
        $total_issued_cost_month = DB::select(
            "SELECT (SUM(rsd.approved_qty) * (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = rsd.cl2comb ORDER BY created_at DESC)) as total_cost
            FROM csrw_request_stocks_details  as rsd
            JOIN csrw_request_stocks as rs on rsd.request_stocks_id = rs.id
            WHERE rs.received_date BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
            GROUP BY rsd.cl2comb, rs.received_date;"
        );

        // most requested items
        $most_requested_month = DB::select(
            "SELECT TOP 5 hc.cl2desc as item,  SUM(rsd.requested_qty) as quantity
            FROM csrw_request_stocks_details as rsd
            JOIN hclass2 as hc ON rsd.cl2comb = hc.cl2comb
            WHERE rsd.created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
            GROUP BY rsd.cl2comb, hc.cl2desc
            ORDER BY quantity DESC;"
        );

        $new_stocks = DB::select(
            "SELECT TOP 5 h2.cl2desc as item, csr.expiration_date as expiration_date
            FROM csrw_csr_stocks_med_supp as csr
            JOIN hclass2 as h2 ON csr.cl2comb = h2.cl2comb
            ORDER BY csr.expiration_date DESC;"
        );


        return Inertia::render('Csr/Dashboard/Index', [
            'pending_requests' => $pending_requests,
            'completed_requests_month' => $completed_requests_month,
            'pending_requests_month' => $pending_requests_month,
            'total_issued_cost_month' => $total_issued_cost_month,
            'most_requested_month' => $most_requested_month,
            'new_stocks' => $new_stocks,
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
