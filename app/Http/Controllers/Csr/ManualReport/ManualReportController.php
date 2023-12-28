<?php

namespace App\Http\Controllers\Csr\ManualReport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManualReportController extends Controller
{
    public function index()
    {
        $reports = array();

        return Inertia::render('Csr/ManualReport/Index', [
            'reports' => $reports
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
