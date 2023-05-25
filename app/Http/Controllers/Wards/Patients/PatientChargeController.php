<?php

namespace App\Http\Controllers\Wards\Patients;

use App\Http\Controllers\Controller;
use App\Models\CsrwCode;
use App\Models\Item;
use App\Models\Miscellaneous;
use App\Models\Patient;
use App\Models\PatientAccount;
use App\Models\PatientCharge;
use App\Models\PatientChargeLogs;
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

        // TODO optimize, find a way to combine bills and tanks query
        // TANKS = drugs and med (oxygen), compressed air, carbon dioxide
        $tanks = DB::select("SELECT CONCAT(hdmhdr.dmdcomb, hdmhdr.dmdctr) as itemcode,
                            hdmhdrsub.dmhdrsub, hdmhdrprice.unitcode,
                            CONCAT(hgen.gendesc, ' ', dmdnost, ' ', hdmhdr.dmdnnostp, ' ', hstre.stredesc, ' ', hform.formdesc, ' ', hroute.rtedesc) AS itemDesc,
                            (SELECT TOP 1 dmselprice FROM hdmhdrprice WHERE dmdcomb = hdmhdrsub.dmdcomb ORDER BY dmdprdte DESC) as 'price'
                            FROM hdmhdr
                            JOIN hpatchrg ON CONCAT(hdmhdr.dmdcomb, hdmhdr.dmdctr) = hpatchrg.itemcode
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
                            AND hpatchrg.enccode = ?
                            GROUP BY hdmhdr.dmdcomb, hdmhdr.dmdctr, hdmhdrsub.dmhdrsub, hdmhdrprice.unitcode, hdmhdrsub.dmdcomb, hgen.gendesc, hdmhdr.dmdnost, hdmhdr.dmdnnostp, hstre.stredesc, hform.formdesc, hroute.rtedesc;
                        ", [$request->enccode]);


        return Inertia::render('Wards/Patients/Bill/Index', [
            'bills' => $bills,
            'medicalSupplies' => $medicalSupplies,
            'misc' => $misc,
            'tanks' => $tanks,
        ]);
    }


    public function store(Request $request)
    {
        // dd($request);

        $entryby = Auth::user()->employeeid;

        $srcchrg = 'WARD';

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
                $patientChargeDate = PatientCharge::create([
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
                    'entryby' => $entryby,
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

                $remaining_qty_to_charge = $item['qtyToCharge']; // 15
                $newStockQty = 0;

                while ($remaining_qty_to_charge > 0) {
                    // check the current item that is going to expire and qty is 0
                    $wardStock = WardsStocks::where('cl2comb', $item['itemCode'])
                        ->where('quantity', '!=', 0)
                        ->where('location', $authWardcode->wardcode)
                        ->orderBy('expiration_date', 'ASC')
                        ->first(); // 10

                    // execute if row selected qty is enough
                    if ($wardStock->quantity >= $remaining_qty_to_charge) {
                        PatientChargeLogs::create([
                            'enccode' => $enccode,
                            'acctno' => $acctno->paacctno,
                            'ward_stocks_id' => $wardStock->id,
                            'itemcode' => $wardStock->cl2comb,
                            'manufactured_date' => $wardStock->manufactured_date == null ? null : $wardStock->manufactured_date,
                            'delivery_date' => $wardStock->delivery_date == null ? null : $wardStock->delivery_date,
                            'expiration_date' => $wardStock->expiration_date == null ? null : $wardStock->expiration_date,
                            'quantity' => $remaining_qty_to_charge,
                            'price_per_piece' => (int)$item['price'] == null ? null : (int)$item['price'],
                            'price_total' => (int)$remaining_qty_to_charge * (int)$item['price'],
                            // 'pcchrgdte' => Carbon::now(),
                            'pcchrgdte' => $patientChargeDate->pcchrgdte,
                            'entry_at' => $authWardcode->wardcode,
                            'entry_by' => $entryby,
                        ]);

                        // getting the new qty of current editing ward stock
                        $newStockQty = $wardStock->quantity - $remaining_qty_to_charge;
                        // setting the new value of remaining_qty_to_charge
                        $remaining_qty_to_charge = $remaining_qty_to_charge - $wardStock->quantity;

                        $wardStock::where('id', $wardStock->id)
                            ->update([
                                'quantity' => $newStockQty,
                            ]);
                    } else {
                        // calculate the value of quantity to insert based on the specific
                        // stock that will be given
                        $qty = ($wardStock->quantity - $remaining_qty_to_charge) + $remaining_qty_to_charge;

                        PatientChargeLogs::create([
                            'enccode' => $enccode,
                            'acctno' => $acctno->paacctno,
                            'ward_stocks_id' => $wardStock->id,
                            'itemcode' => $wardStock->cl2comb,
                            'manufactured_date' => $wardStock->manufactured_date == null ? null : $wardStock->manufactured_date,
                            'delivery_date' => $wardStock->delivery_date == null ? null : $wardStock->delivery_date,
                            'expiration_date' => $wardStock->expiration_date == null ? null : $wardStock->expiration_date,
                            'quantity' => $qty,
                            'price_per_piece' => (int)$item['price'] == null ? null : (int)$item['price'],
                            'price_total' => (int)$qty * (int)$item['price'],
                            // 'pcchrgdte' => Carbon::now(),
                            'pcchrgdte' => $patientChargeDate->pcchrgdte,
                            'entry_at' => $authWardcode->wardcode,
                            'entry_by' => $entryby,
                        ]);

                        $remaining_qty_to_charge = $remaining_qty_to_charge - $wardStock->quantity;

                        $wardStock::where('id', $wardStock->id)
                            ->update([
                                'quantity' => 0
                            ]);
                    }
                }

                // PatientCharge::create([
                //     'enccode' => $enccode,
                //     'hpercode' => $hospitalNumber,
                //     'upicode' => null,
                //     'pcchrgcod' => $pcchrgcod, // charge slip no.
                //     'pcchrgdte' => Carbon::now(),
                //     'chargcode' => $item['typeOfCharge'], // type of charge (chrgcode from hcharge)
                //     'uomcode' => $item['unit'], // unit
                //     'pchrgqty' =>  $item['qtyToCharge'],
                //     'pchrgup' => $item['price'],
                //     'pcchrgamt' => $item['total'],
                //     'pcstat' => 'A', // always A
                //     'pclock' => 'N', // always N
                //     'updsw' => 'N', // always N
                //     'confdl' => 'N', // always N
                //     'srcchrg' => $srcchrg,
                //     'pcdisch' => 'Y',
                //     'acctno' => $acctno->paacctno, // SELECT * FROM hpatacct --pacctno
                //     'itemcode' => $item['itemCode'], // cl2comb or hmisc hmcode
                //     'entryby' => $entryby,
                //     'orinclst' => null, // null
                //     'compense' => null, // always null
                //     'proccode' => null, // always null
                //     'discount' => null, // always null
                //     'disamt' => null, // always null
                //     'discbal' => null, // always null
                //     'phicamt' => null, // always null
                //     'rvscode' => null, // always null
                //     'licno' => null, // always null
                //     'hpatkey' => null, // always null
                //     'time_frequency' => null, // always null
                //     'unit_frequency' => null, // always null
                //     'qtyintake' => null, // always null
                //     'uomintake' => null, // always null
                // ]);

                // reset
                $srcchrg = '';
            }

            if ($item['typeOfCharge'] == 'MISC') {
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
                    'entryby' => $entryby,
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

            // Drugs and Meds (Oxygen), carbon dioxide
            if ($item['typeOfCharge'] == 'DRUMD') {
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
                    'entryby' => $entryby,
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

            // /Compressed Air
            if ($item['typeOfCharge'] == 'DRUMF') {
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
                    'entryby' => $entryby,
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
        // dd($request);
    }


    public function destroy($id)
    {
        //
    }
}
