<?php

namespace App\Http\Controllers\Wards\RequestMeds;

use App\Http\Controllers\Controller;
use App\Models\FundSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RequestMedsController extends Controller
{
    public function index()
    {
        $medicines = [];
        $resultArray = DB::select(
            "SELECT dmdcomb, dmdctr, drug_concat
                FROM hdmhdr
                WHERE dmdstat = 'A'
                AND drug_concat IS NOT NULL
                ORDER BY drug_concat ASC;"
        );
        foreach ($resultArray as $result) {
            $medicines[] = [
                'dmdcomb' => $result->dmdcomb,
                'dmdctr' => $result->dmdctr,
                'drug_concat' => explode('_,', $result->drug_concat)
            ];
        }

        $fundSource = FundSource::get(['id', 'fsid', 'fsName', 'cluster_code']);

        return Inertia::render('Wards/MedicineStocks/Index', [
            'medicines' => $medicines,
            'fundSource' => $fundSource,
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
