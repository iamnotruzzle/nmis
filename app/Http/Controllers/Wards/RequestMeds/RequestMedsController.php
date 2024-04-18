<?php

namespace App\Http\Controllers\Wards\RequestMeds;

use App\Http\Controllers\Controller;
use App\Models\FundSource;
use App\Models\MedsRequest;
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
                AND exists (select * from [hospital].[dbo].[hgen] inner join [hospital].[dbo].[hdruggrp] on [hospital].[dbo].[hdruggrp].[gencode] = [hospital].[dbo].[hgen].[gencode] where [hospital].[dbo].[hdmhdr].[grpcode] = [hospital].[dbo].[hdruggrp].[grpcode])
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

        $medsRequest = MedsRequest::paginate(10);

        return Inertia::render('Wards/MedicineStocks/Index', [
            'medicines' => $medicines,
            'fundSource' => $fundSource,
            'medsRequest' => $medsRequest,
        ]);
    }

    public function store(Request $request)
    {
        dd($request);
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
