<?php

namespace App\Http\Controllers\Csr\ReturnedItems;

use App\Http\Controllers\Controller;
use App\Models\CsrItemConversion;
use App\Models\Location;
use App\Models\ReturnedItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Models\Sessions;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReturnedItemsController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::select(
            "SELECT returned_items.id, returned_items.ris_no, returned_items.cl2comb, item.cl2desc as item,
                    returned_items.quantity, returned_items.restocked_quantity,
                    ward.wardcode, ward.wardname as ward,
                    employee.firstname, employee.lastname, returned_items.remarks, returned_items.created_at
                FROM csrw_csr_returned_items as returned_items
                JOIN hclass2 as item ON item.cl2comb = returned_items.cl2comb
                JOIN hward as ward ON ward.wardcode = returned_items.[from]
                JOIN hpersonal as employee ON employee.employeeid = returned_items.returned_by;"
        );
        $returnedItems = array();

        foreach ($result as $r) {
            $returnedItems[] = (object) [
                'id' => $r->id,
                'ris_no' => $r->ris_no,
                'cl2comb' => $r->cl2comb,
                'item' => $r->item,
                'quantity' => $r->quantity,
                'restocked_quantity' => $r->restocked_quantity,
                'wardcode' => $r->wardcode,
                'ward' => $r->ward,
                'returned_by' => $r->firstname . ' ' . $r->lastname,
                'remarks' => $r->remarks,
                'created_at' => $r->created_at,
            ];
        }

        $locations = Location::where('wardstat', 'A')
            ->orderBy('wardname', 'ASC')
            ->get();

        return Inertia::render('Csr/ReturnedItems/Index', [
            'returnedItems' => $returnedItems,
            'locations' => $locations,
        ]);
    }

    public function store(Request $request)
    {
        // dd($request);

        $returnedItems = ReturnedItems::where('id', $request->id)->first();
        // dd($returnedItems);

        $stock = CsrItemConversion::where('id', $returnedItems->item_conversion_id)->first();

        $returnedItems->update(
            [
                'quantity' => $returnedItems->quantity - $request->quantity,
                'restocked_quantity' => $returnedItems->restocked_quantity + $request->quantity,
            ]
        );

        CsrItemConversion::where('id', $returnedItems->item_conversion_id)
            ->update(
                [
                    'quantity_after' => $stock->quantity_after + $request->quantity,
                    'total_issued_qty' => $stock->total_issued_qty - $request->quantity,
                ]
            );

        return Redirect::route('returneditems.index');
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
