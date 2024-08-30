<?php

namespace App\Http\Controllers\Csr\ReturnedItems;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReturnedItemsController extends Controller
{
    public function index(Request $request)
    {
        $result = DB::select(
            "SELECT returned_items.id, returned_items.ris_no, item.cl2desc as item, returned_items.quantity, ward.wardcode, ward.wardname as ward, employee.firstname, employee.lastname, returned_items.remarks, returned_items.created_at
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
                'item' => $r->item,
                'quantity' => $r->quantity,
                'wardcode' => $r->wardcode,
                'ward' => $r->ward,
                'returned_by' => $r->firstname . ' ' . $r->lastname,
                'remarks' => $r->remarks,
                'created_at' => $r->created_at,
            ];
        }

        return Inertia::render('Csr/ReturnedItems/Index', [
            'returnedItems' => $returnedItems,
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
