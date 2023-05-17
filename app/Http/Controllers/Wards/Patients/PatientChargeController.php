<?php

namespace App\Http\Controllers\Wards\Patients;

use App\Http\Controllers\Controller;
use App\Models\CsrwCode;
use App\Models\Item;
use App\Models\Miscellaneous;
use App\Models\Patient;
use App\Models\PatientAccount;
use App\Models\PatientCharge;
use App\Models\TypeOfCharge;
use App\Models\WardsStocks;
use Carbon\Carbon;
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
        // dd($request);

        $srcchrg = '';

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

        // create csrw_code
        $csrw_code = CsrwCode::create([
            'charge_desc' => 'a',
            'created_at' => Carbon::now(),
        ]);
        // get count of csrcode where year is NOW
        $currentCodeCount = CsrwCode::whereYear('created_at', Carbon::now()->year)->count();
        // CW = Central supply room Ward
        $pcchrgcod = 'CW' . Carbon::now()->format('y') . '-' . sprintf('%06d', $currentCodeCount);

        // get patient account number
        $acctno = PatientAccount::where('enccode', $enccode)->first(['paacctno']);

        foreach ($itemsToBillList as $item) {

            if ($item['typeOfCharge'] == 'DRUMN') {
                $srcchrg = 'WARD';
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
                        $srcchrg = 'WARD';
                        $remaining_qty_to_charge = $remaining_qty_to_charge - $wardStock->quantity;

                        $wardStock::where('id', $wardStock->id)
                            ->update([
                                'quantity' => 0
                            ]);
                    }
                }

                PatientCharge::create([
                    'enccode' => $enccode,
                    'hpercode' => $hospitalNumber,
                    'upicode' => null,
                    'pcchrgcod' => $pcchrgcod, // charge slip no.
                    'pcchrgdte' => Carbon::now(),
                    'chargcode' => $item['typeOfCharge'], // type of charge (chrgcode from hcharge)
                    'uomcode' => $item['unit'], // unit
                    'pchrgqty' =>  $item['qtyToCharge'],
                    'pchrgup' => $item['price'],
                    'pcchrgamt' => $item['total'],
                    'pcstat' => 'A', // always A
                    'pclock' => 'N', // always N
                    'updsw' => 'N', // always N
                    'confdl' => 'N', // always N
                    'srcchrg' => $srcchrg,
                    'pcdisch' => 'Y',
                    'acctno' => $acctno->paacctno, // SELECT * FROM hpatacct --pacctno
                    'itemcode' => $item['itemCode'], // cl2comb or hmisc hmcode
                    'entryby' => Auth::user()->employeeid,
                    'orinclst' => null, // null
                    'compense' => null, // always null
                    'proccode' => null, // always null
                    'discount' => null, // always null
                    'disamt' => null, // always null
                    'discbal' => null, // always null
                    'phicamt' => null, // always null
                    'rvscode' => null, // always null
                    'licno' => null, // always null
                    'hpatkey' => null, // always null
                    'time_frequency' => null, // always null
                    'unit_frequency' => null, // always null
                    'qtyintake' => null, // always null
                    'uomintake' => null, // always null
                ]);

                // reset
                $srcchrg = '';
            }

            if ($item['typeOfCharge'] == 'MISC') {
                $srcchrg = 'WARD';

                PatientCharge::create([
                    'enccode' => $enccode,
                    'hpercode' => $hospitalNumber,
                    'upicode' => null,
                    'pcchrgcod' => $pcchrgcod, // charge slip no.
                    'pcchrgdte' => Carbon::now(),
                    'chargcode' => $item['typeOfCharge'], // type of charge (chrgcode from hcharge)
                    'uomcode' => $item['unit'], // unit
                    'pchrgqty' =>  $item['qtyToCharge'],
                    'pchrgup' => $item['price'],
                    'pcchrgamt' => $item['total'],
                    'pcstat' => 'A', // always A
                    'pclock' => 'N', // always N
                    'updsw' => 'N', // always N
                    'confdl' => 'N', // always N
                    'srcchrg' => $srcchrg,
                    'pcdisch' => 'Y',
                    'acctno' => $acctno->paacctno, // SELECT * FROM hpatacct --pacctno
                    'itemcode' => $item['itemCode'], // cl2comb or hmisc hmcode
                    'entryby' => Auth::user()->employeeid,
                    'orinclst' => null, // null
                    'compense' => null, // always null
                    'proccode' => null, // always null
                    'discount' => null, // always null
                    'disamt' => null, // always null
                    'discbal' => null, // always null
                    'phicamt' => null, // always null
                    'rvscode' => null, // always null
                    'licno' => null, // always null
                    'hpatkey' => null, // always null
                    'time_frequency' => null, // always null
                    'unit_frequency' => null, // always null
                    'qtyintake' => null, // always null
                    'uomintake' => null, // always null
                ]);

                // reset
                $srcchrg = '';
            }
        }

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
