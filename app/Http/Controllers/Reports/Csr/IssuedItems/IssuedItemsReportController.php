<?php

namespace App\Http\Controllers\Reports\Csr\IssuedItems;

use App\Exports\IssuedItemsReport;
use App\Http\Controllers\Controller;
use App\Models\RequestStocks;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IssuedItemsReportController extends Controller
{
    public function export(Request $request)
    {
        // dd($request->id);
        $issuedItems = RequestStocks::with([
            'requested_at_details:wardcode,wardname',
            'requested_by_details:employeeid,firstname,middlename,lastname',
            'approved_by_details',
            'request_stocks_details.item_details:cl2comb,cl2desc',
            'request_stocks_details'
        ])
            ->where('id', $request->id)
            ->first();

        // dd($issuedItems);

        foreach ($issuedItems->request_stocks_details as $e) {
            $reports[] = (object) [
                // 'requested_at' => $issuedItems->requested_at_details->wardname,
                'item' => $e->item_details->cl2desc,
                'requested_qty' => $e->requested_qty,
                'approved_qty' => $e->approved_qty,
                'remarks' => $e->remarks,
            ];
        }

        // dd($reports);

        return Excel::download(new IssuedItemsReport($reports), 'issued.xlsx');
    }
}
