<?php

namespace App\Http\Controllers\Csr\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CsrStocksMedicalSupplies;
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
        $pending_requests = RequestStocks::where('status', 'PENDING')->count();
        $cancelled_requests = RequestStocks::where('status', 'CANCELLED')->count();
        $completed_requests = RequestStocks::where('status', 'RECEIVED')->count();


        // most requested items
        $most_requested_month = DB::select(
            "SELECT TOP 5 hc.cl2desc as item,  SUM(rsd.requested_qty) as quantity
            FROM csrw_request_stocks_details as rsd
            JOIN hclass2 as hc ON rsd.cl2comb = hc.cl2comb
            WHERE rsd.created_at BETWEEN DATEADD(month, DATEDIFF(month, 0, getdate()), 0) AND getdate()
            GROUP BY rsd.cl2comb, hc.cl2desc
            ORDER BY quantity DESC;"
        );

        $about_to_expire = CsrStocksMedicalSupplies::with('itemDetail:cl2comb,cl2desc')
            ->orderBy('expiration_date', 'ASC')->limit(10)->get();


        return Inertia::render('Csr/Dashboard/Index', [
            'pending_requests' => $pending_requests,
            'cancelled_requests' => $cancelled_requests,
            'completed_requests' => $completed_requests,
            'most_requested_month' => $most_requested_month,
            'about_to_expire' => $about_to_expire,
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
