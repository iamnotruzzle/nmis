<?php

namespace App\Http\Controllers\Wards\Patients;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Miscellaneous;
use App\Models\Patient;
use App\Models\TypeOfCharge;
use App\Models\WardsStocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PatientChargeController extends Controller
{
    public function index(Request $request)
    {
        $enccode = $request->enccode;

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        // get wards current stocks / MEDICAL SUPPLIES
        $medicalSupplies = DB::table('hclass2')
            ->join('csrw_wards_stocks', 'csrw_wards_stocks.cl2comb', '=', 'hclass2.cl2comb')
            ->select(DB::raw("hclass2.cl2comb, hclass2.cl2desc, hclass2.uomcode, SUM(csrw_wards_stocks.quantity) as quantity, (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = csrw_wards_stocks.cl2comb ORDER BY created_at DESC) as 'price'"))
            ->where('csrw_wards_stocks.location', $authWardcode->wardcode)
            ->groupBy('hclass2.cl2comb', 'hclass2.cl2desc', 'hclass2.uomcode', 'csrw_wards_stocks.cl2comb')
            ->get();

        // get miscellaneous / miscellaneous
        $misc = Miscellaneous::with('unit')
            ->where('hmstat', 'A')
            ->get(['hmcode', 'hmdesc', 'hmamt', 'uomcode']);
        // dd($misc);

        // get patients bills
        $bills = Patient::with([
            'admissionDateBill',
        ])
            // this will filter patients that hasn't been discharge
            ->whereHas('admissionDateBill', function ($q) use ($enccode) {
                $q->where('enccode', $enccode);
            })
            ->first();

        return Inertia::render('Wards/Patients/Bill/Index', [
            'bills' => $bills,
            'medicalSupplies' => $medicalSupplies,
            'misc' => $misc,
        ]);
    }


    public function store(Request $request)
    {
        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $enccode = $request->enccode;
        $hospitalNumber = $request->hospitalNumber;
        $itemsToBillList = $request->itemsToBillList;

        foreach ($itemsToBillList as $item) {

            if ($item['typeOfCharge'] == 'DRUMN') {
                $remaining_qty_to_charge = $item['qtyToCharge'];
                $newStockQty = 0;

                while ($remaining_qty_to_charge > 0) {
                    // check the current item that is going to expire and qty is 0
                    $wardStock = WardsStocks::where('cl2comb', $item['itemCode'])
                        ->where('quantity', '!=', 0)
                        ->where('location', $authWardcode->wardcode)
                        ->orderBy('expiration_date', 'ASC')
                        ->first();

                    // execute if row selected is qty is enough
                    if ($wardStock->quantity >= $remaining_qty_to_charge) {
                        // getting the new qty of current editing ward stock
                        $newStockQty = $wardStock->quantity - $remaining_qty_to_charge;
                        // setting the new value of remaining_qty_to_charge
                        $remaining_qty_to_charge = $remaining_qty_to_charge - $wardStock->quantity;

                        $wardStock::where('id', $wardStock->id)
                            ->update([
                                'quantity' => $newStockQty,
                            ]);
                    } else {
                        $remaining_qty_to_charge = $remaining_qty_to_charge - $wardStock->quantity;

                        $wardStock::where('id', $wardStock->id)
                            ->update([
                                'quantity' => 0
                            ]);
                    }
                }
            }

            if ($item['typeOfCharge'] == 'MISC') {
                $remaining_qty_to_charge = $item['qtyToCharge'];
                $newStockQty = 0;

                while ($remaining_qty_to_charge > 0) {
                    // check the current item that is going to expire and qty is 0
                    $wardStock = WardsStocks::where('cl2comb', $item['itemCode'])
                        ->where('quantity', '!=', 0)
                        ->where('location', $authWardcode->wardcode)
                        ->orderBy('expiration_date', 'ASC')
                        ->first();

                    // execute if row selected is qty is enough
                    if ($wardStock->quantity >= $remaining_qty_to_charge) {
                        // getting the new qty of current editing ward stock
                        $newStockQty = $wardStock->quantity - $remaining_qty_to_charge;
                        // setting the new value of remaining_qty_to_charge
                        $remaining_qty_to_charge = $remaining_qty_to_charge - $wardStock->quantity;

                        $wardStock::where('id', $wardStock->id)
                            ->update([
                                'quantity' => $newStockQty,
                            ]);
                    } else {
                        $remaining_qty_to_charge = $remaining_qty_to_charge - $wardStock->quantity;

                        $wardStock::where('id', $wardStock->id)
                            ->update([
                                'quantity' => 0
                            ]);
                    }
                }
            }
        }

        // return Redirect::route('patientcharge.index');
        return Redirect::back();
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
