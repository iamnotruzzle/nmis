<?php

namespace App\Http\Controllers\Csr\IssueItem;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\RequestStock;
use App\Models\WardStocks;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class IssueItemController extends Controller
{
    public function index(Request $request)
    {
        $searchString = $request->search;

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $items = Item::where('cl2stat', 'A')
            ->orderBy('cl2desc', 'ASC')
            ->get(['cl2comb', 'cl2desc']);

        $stocks = WardStocks::with('itemDetail')
            // ->whereHas('itemDetail', function ($q) use ($searchString) {
            //     $q->where('cl2desc', 'LIKE', '%' . $searchString . '%')
            //         ->orWhere('batch_no', 'LIKE', '%' . $searchString . '%');
            // })
            ->orderBy('expiration_date', 'desc')
            ->paginate(15);

        $requestStocks = RequestStock::with([
            'itemDetail',
            'requested_by_details',
            'requested_at_details',
            'approved_by_details'
        ])
            ->whereHas('requested_at_details', function ($q) use ($searchString) {
                $q->where('wardname', 'LIKE', '%' . $searchString . '%');
            })
            ->when(
                $request->from,
                function ($query, $value) {
                    $query->whereDate('created_at', '>=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->when(
                $request->to,
                function ($query, $value) {
                    $query->whereDate('created_at', '<=', Carbon::parse($value)->setTimezone('Asia/Manila'));
                }
            )
            ->paginate(15);

        return Inertia::render('Csr/IssueItems/Index', [
            'authWardcode' => $authWardcode,
            'items' => $items,
            'stocks' => $stocks,
            'requestStocks' => $requestStocks,
        ]);
    }

    public function store(Request $request)
    {
        // TODO create a validation to check if the approved_qty is enough to the
        // total quantity of the item's stock

        $requestStockId = $request->id;

        $request->validate([
            'approved_qty' => 'required|numeric',
        ]);

        $requeststock = RequestStock::where('id', $requestStockId)->first();

        $requeststock->update([
            'approved_qty' => $request->approved_qty,
            'status' => 'APPROVED',
            'approved_by' => $request->approved_by,
        ]);

        return Redirect::route('requeststocks.index');
    }

    public function update(RequestStock $requeststock, Request $request)
    {
        $request->validate([
            'cl2comb' => 'required',
            'requested_qty' => 'required|numeric',
        ]);

        $requeststock->update([
            'cl2comb' => $request->cl2comb,
            'requested_qty' => $request->requested_qty,
        ]);

        return Redirect::route('requeststocks.index');
    }

    public function destroy(RequestStock $requeststock)
    {
        // $requeststock->delete();

        return Redirect::route('requeststocks.index');
    }
}
