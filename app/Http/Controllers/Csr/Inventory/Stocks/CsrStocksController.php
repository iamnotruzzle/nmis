<?php

namespace App\Http\Controllers\Csr\Inventory\Stocks;

use App\Http\Controllers\Controller;
use App\Models\CsrStocks;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class CsrStocksController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;


        $items = Item::where('cl2stat', 'A')
            ->orderBy('cl2desc', 'ASC')
            ->get(['cl2comb', 'cl2desc']);

        $stocks = CsrStocks::with('itemDetail')
            ->whereHas('itemDetail', function ($q) use ($searchString) {
                $q->where('cl2desc', 'LIKE', '%' . $searchString . '%')
                    ->orWhere('batch_no', 'LIKE', '%' . $searchString . '%');
            })
            ->when(
                $request->from_md,
                function ($query, $value) {
                    $query->whereDate('manufactured_date', '>=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->to_md,
                function ($query, $value) {
                    $query->whereDate('manufactured_date', '<=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->from_dd,
                function ($query, $value) {
                    $query->whereDate('delivered_date', '>=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->to_dd,
                function ($query, $value) {
                    $query->whereDate('delivered_date', '<=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->from_ed,
                function ($query, $value) {
                    $query->whereDate('expiration_date', '>=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->to_ed,
                function ($query, $value) {
                    $query->whereDate('expiration_date', '<=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->orderBy('expiration_date', 'asc')
            ->paginate(15);

        return Inertia::render('Csr/Inventory/Stocks/Index', [
            'items' => $items,
            'stocks' => $stocks,
        ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'batch_no' => 'required',
            'cl2comb' => 'required',
            'quantity' => 'required|numeric',
            'expiration_date' => 'required',
        ]);

        $stocks = CsrStocks::create([
            'batch_no' => $request->batch_no,
            'cl2comb' => $request->cl2comb,
            'quantity' => $request->quantity,
            'manufactured_date' => $request->manufactured_date == null ? null : Carbon::parse($request->manufactured_date)->setTimezone('Asia/Manila'),
            'delivered_date' => $request->delivered_date == null ? null : Carbon::parse($request->delivered_date)->setTimezone('Asia/Manila'),
            'expiration_date' => $request->expiration_date == null ? null : Carbon::parse($request->expiration_date)->setTimezone('Asia/Manila'),
        ]);

        return Redirect::route('csrstocks.index');
    }

    public function update(CsrStocks $csrstock, Request $request)
    {
        $request->validate([
            'batch_no' => 'required',
            'cl2comb' => 'required',
            'quantity' => 'required|numeric',
            'expiration_date' => 'required',
        ]);

        $csrstock->update([
            'batch_no' => $request->batch_no,
            'cl2comb' => $request->cl2comb,
            'quantity' => $request->quantity,
            'manufactured_date' => $request->manufactured_date == null ? null : Carbon::parse($request->manufactured_date)->setTimezone('Asia/Manila'),
            'delivered_date' => $request->delivered_date == null ? null : Carbon::parse($request->delivered_date)->setTimezone('Asia/Manila'),
            'expiration_date' => $request->expiration_date == null ? null : Carbon::parse($request->expiration_date)->setTimezone('Asia/Manila'),
        ]);

        return Redirect::route('csrstocks.index');
    }

    public function destroy(CsrStocks $csrstock)
    {
        $csrstock->delete();

        return Redirect::route('csrstocks.index');
    }
}
