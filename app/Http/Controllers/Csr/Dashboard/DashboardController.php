<?php

namespace App\Http\Controllers\Csr\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\RequestStocks;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        $completed_request_this_month = RequestStocks::where('status', 'RECEIVED')
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
        $completed_request_week = RequestStocks::where('status', 'RECEIVED')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $completed_request_today = RequestStocks::where('status', 'RECEIVED')
            ->where('created_at', Carbon::today())->count();

        // dd($completed_request_this_week);

        return Inertia::render('Csr/Dashboard/Index', [
            'completed_request_this_month' => $completed_request_this_month,
            'completed_request_week' => $completed_request_week,
            'completed_request_today' => $completed_request_today,
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
