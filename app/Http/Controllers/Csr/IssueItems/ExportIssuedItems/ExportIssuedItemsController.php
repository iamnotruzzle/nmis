<?php

namespace App\Http\Controllers\Csr\IssueItems\ExportIssuedItems;

use App\Http\Controllers\Controller;
use App\Models\RequestStocks;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExportIssuedItemsController extends Controller
{
    public function index(Request $request)
    {
        $issuedItems = RequestStocks::with([
            'requested_at_details:wardcode,wardname',
            'requested_by_details:employeeid,firstname,middlename,lastname',
            'approved_by_details',
            'request_stocks_details.item_details:cl2comb,cl2desc',
            'request_stocks_details'
        ])
            ->where('id', (int)$request->id)
            ->first();

        dd($issuedItems);

        return Inertia::render('Csr/IssueItems/Index', [
            'issuedItems' => $issuedItems
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
