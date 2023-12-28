<?php

namespace App\Http\Controllers\Csr\ManualReport;

use App\Http\Controllers\Controller;
use App\Models\CsrManualReport;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ManualReportController extends Controller
{
    public function index()
    {
        $manual_reports = CsrManualReport::paginate(15);

        return Inertia::render('Csr/ManualReport/Index', [
            'manual_reports' => $manual_reports
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
