<?php

namespace App\Http\Controllers\Reports\Csr\IssuedTankItems;

use App\Exports\IssuedTanksReport;
use App\Http\Controllers\Controller;
use App\Models\RequestTankStocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class IssuedTankItemsReportController extends Controller
{
    public function export(Request $request)
    {
        // dd($request->id);
        $issuedItems = RequestTankStocks::with([
            'requested_at_details:wardcode,wardname',
            'requested_by_details:employeeid,firstname,middlename,lastname',
            'approved_by_details',
            'request_stocks_details'
        ])
            ->where('id', $request->id)
            ->first();

        $tank = DB::select("SELECT CONCAT(hdmhdr.dmdcomb, hdmhdr.dmdctr) as itemcode,
                                hdmhdrsub.dmhdrsub,
                                (SELECT TOP 1 unitcode FROM hdmhdrprice WHERE dmdcomb = hdmhdrsub.dmdcomb ORDER BY dmdprdte DESC) as 'unitcode',
                                CONCAT(hgen.gendesc, ' ', dmdnost, ' ', hdmhdr.dmdnnostp, ' ', hstre.stredesc, ' ', hform.formdesc, ' ', hroute.rtedesc) AS itemDesc,
                                (SELECT TOP 1 dmselprice FROM hdmhdrprice WHERE dmdcomb = hdmhdrsub.dmdcomb ORDER BY dmdprdte DESC) as 'price'
                            FROM hdmhdr
                            JOIN hdmhdrsub ON hdmhdr.dmdcomb = hdmhdrsub.dmdcomb
                            JOIN hdmhdrprice ON hdmhdrsub.dmdcomb = hdmhdrprice.dmdcomb
                            JOIN hdruggrp ON hdmhdr.grpcode = hdruggrp.grpcode
                            JOIN hgen ON hgen.gencode = hdruggrp.gencode
                            JOIN hstre ON hdmhdr.strecode = hstre.strecode
                            JOIN hform ON hdmhdr.formcode = hform.formcode
                            JOIN hroute ON hdmhdr.rtecode = hroute.rtecode
                            WHERE ((hdmhdr.grpcode = '0000000671' )
                            OR (hdmhdr.grpcode = '0000000764'
                            AND hdmhdrsub.dmhdrsub = 'DRUMD' )
                            OR (hdmhdr.dmdcomb = '000000002098'))
                            AND hdmhdrsub.stockbal != 0
                            GROUP BY hdmhdr.dmdcomb, hdmhdr.dmdctr, hdmhdrsub.dmhdrsub, hgen.gendesc, dmdnost, hdmhdr.dmdnnostp, hstre.stredesc, hform.formdesc, hroute.rtedesc, hdmhdrsub.dmdcomb;
                    ");

        foreach ($issuedItems->request_stocks_details as $e) {
            $result = array_filter($tank, function ($item) use ($e) {
                // dd($e->itemcode);
                return $item->itemcode === $e->itemcode;
            });

            $tank_details = array_values($result);
            // dd($tank_details);

            $reports[] = (object) [
                // 'requested_at' => $issuedItems->requested_at_details->wardname,
                'item' => $tank_details[0]->itemDesc,
                'requested_qty' => $e->requested_qty,
                'approved_qty' => $e->approved_qty,
                'remarks' => $e->remarks,
            ];
        }

        // dd($reports);

        return Excel::download(new IssuedTanksReport($reports), 'issued.xlsx');
    }
}
