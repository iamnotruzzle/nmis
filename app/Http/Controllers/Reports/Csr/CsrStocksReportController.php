<?php

namespace App\Http\Controllers\Reports\Csr;

use App\Exports\CsrStocksReport;
use App\Http\Controllers\Controller;
use App\Models\CsrStocks;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CsrStocksReportController extends Controller
{
    public function export()
    {
        $csrStocks = CsrStocks::get(['id', 'batch_no', 'cl2comb', 'brand', 'chrgcode', 'quantity', 'manufactured_date', 'delivered_date', 'expiration_date']);

        return Excel::download(new CsrStocksReport($csrStocks), 'csr_stocks.xlsx');
    }
}
