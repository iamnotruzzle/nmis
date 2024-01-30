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
use App\Models\PatientChargeReturnLogs;
use App\Models\TypeOfCharge;
use App\Models\WardsStocksMedSupp;
use App\Models\WardStocksTanks;
use App\Rules\StockBalanceNotDeclaredYetRule;
use App\Rules\TankStockBalanceNotDeclearedYet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use stdClass;

class PatientChargeController extends Controller
{
    public function index(Request $request)
    {
        $enccode = $request->enccode;
        $medicalSupplies = array();

        // get auth wardcode
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        // get wards current stocks / MEDICAL SUPPLIES
        $stocksFromCsr = DB::table('hclass2')
            ->join('csrw_wards_stocks_med_supp', 'csrw_wards_stocks_med_supp.cl2comb', '=', 'hclass2.cl2comb')
            ->join('csrw_request_stocks', 'csrw_request_stocks.id', '=', 'csrw_wards_stocks_med_supp.request_stocks_id')
            ->select(DB::raw("hclass2.cl2comb, hclass2.cl2desc, hclass2.uomcode, SUM(csrw_wards_stocks_med_supp.quantity) as quantity, (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = csrw_wards_stocks_med_supp.cl2comb ORDER BY created_at DESC) as 'price'"))
            ->where('csrw_wards_stocks_med_supp.location', $authWardcode->wardcode)
            ->where('csrw_wards_stocks_med_supp.expiration_date', '>', Carbon::today())
            ->where('csrw_request_stocks.status', 'RECEIVED')
            ->groupBy('hclass2.cl2comb', 'hclass2.cl2desc', 'hclass2.uomcode', 'csrw_wards_stocks_med_supp.cl2comb')
            ->get();

        $stocksConvertedAndConsignment = DB::table('hclass2')
            ->join('csrw_wards_stocks_med_supp', 'csrw_wards_stocks_med_supp.cl2comb', '=', 'hclass2.cl2comb')
            // ->join('csrw_request_stocks', 'csrw_request_stocks.id', '=', 'csrw_wards_stocks_med_supp.request_stocks_id')
            ->select(DB::raw("hclass2.cl2comb, hclass2.cl2desc, hclass2.uomcode, SUM(csrw_wards_stocks_med_supp.quantity) as quantity, (SELECT TOP 1 selling_price FROM csrw_item_prices WHERE cl2comb = csrw_wards_stocks_med_supp.cl2comb ORDER BY created_at DESC) as 'price'"))
            ->where('csrw_wards_stocks_med_supp.location', $authWardcode->wardcode)
            ->where('csrw_wards_stocks_med_supp.expiration_date', '>', Carbon::today())
            // ->where('csrw_wards_stocks_med_supp.from', 'CSR')
            // ->where('csrw_wards_stocks_med_supp.is_converted', 'y')
            ->where(function ($query) {
                $query->where('csrw_wards_stocks_med_supp.from', 'CSR')
                    ->where('csrw_wards_stocks_med_supp.is_converted', 'y');
            })
            ->orWhere('csrw_wards_stocks_med_supp.from', 'CONSIGNMENT')
            ->groupBy('hclass2.cl2comb', 'hclass2.cl2desc', 'hclass2.uomcode', 'csrw_wards_stocks_med_supp.cl2comb')
            ->get();

        // dd($stocksFromCsr);
        // dd($stocksConvertedAndConsignment);

        foreach ($stocksFromCsr as $s) {
            $medicalSupplies[] = (object) [
                'cl2comb' => $s->cl2comb,
                'cl2desc' => $s->cl2desc,
                'uomcode' => $s->uomcode,
                'quantity' => $s->quantity,
                'price' => $s->price,
            ];
        }

        foreach ($stocksConvertedAndConsignment as $s) {
            $medicalSupplies[] = (object) [
                'cl2comb' => $s->cl2comb,
                'cl2desc' => $s->cl2desc,
                'uomcode' => $s->uomcode,
                'quantity' => $s->quantity,
                'price' => $s->price,
            ];
        }

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
        $data = $request;

        // dd($data->itemsToBillList);

        foreach ($data->itemsToBillList as $e) {
            // DRUGS AND MEDS
            if ($e['typeOfCharge'] == 'DRUMN') {
                $data->validate(
                    [
                        "itemsToBillList.*.itemCode" => ['required', new StockBalanceNotDeclaredYetRule($e['itemCode'])],
                    ],
                );
            }

            // TANKS
            if ($e['typeOfCharge'] == 'DRUMD') {
                $data->validate(
                    [
                        "itemsToBillList.*.itemCode" => ['required', new TankStockBalanceNotDeclearedYet($e['itemCode'])],
                    ],
                );
            }
        }

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


        if ($request->isUpdate == false) {
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
                        $wardStock = WardsStocksMedSupp::where('cl2comb', $item['itemCode'])
                            ->where('quantity', '!=', 0)
                            ->where('location', $authWardcode->wardcode)
                            ->where('expiration_date', '>', Carbon::today())
                            ->orderBy('expiration_date', 'ASC')
                            ->first(); // 10

                        // execute if row selected qty is enough
                        if ($wardStock->quantity >= $remaining_qty_to_charge) {
                            PatientChargeLogs::create([
                                'enccode' => $enccode,
                                'acctno' => $acctno->paacctno,
                                'ward_stocks_id' => $wardStock->id,
                                'brand' => $wardStock->brand,
                                'itemcode' => $wardStock->cl2comb,
                                'from' => $wardStock->from,
                                'manufactured_date' => $wardStock->manufactured_date == null ? null : $wardStock->manufactured_date,
                                'delivery_date' => $wardStock->delivery_date == null ? null : $wardStock->delivery_date,
                                'expiration_date' => $wardStock->expiration_date == null ? null : $wardStock->expiration_date,
                                'quantity' => $remaining_qty_to_charge,
                                'price_per_piece' => (float)$item['price'] == null ? null : (float)$item['price'],
                                'price_total' => (float)$remaining_qty_to_charge * (float)$item['price'],
                                'pcchrgdte' => $patientChargeDate->pcchrgdte,
                                'tscode' => $request->tscode,
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
                                'brand' => $wardStock->brand,
                                'itemcode' => $wardStock->cl2comb,
                                'from' => $wardStock->from,
                                'manufactured_date' => $wardStock->manufactured_date == null ? null : $wardStock->manufactured_date,
                                'delivery_date' => $wardStock->delivery_date == null ? null : $wardStock->delivery_date,
                                'expiration_date' => $wardStock->expiration_date == null ? null : $wardStock->expiration_date,
                                'quantity' => $qty,
                                'price_per_piece' => (float)$item['price'] == null ? null : (float)$item['price'],
                                'price_total' => (float)$qty * (float)$item['price'],
                                'pcchrgdte' => $patientChargeDate->pcchrgdte,
                                'tscode' => $request->tscode,
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
                }

                // MISC
                if ($item['typeOfCharge'] == 'MISC') {
                    $patientMiscChargeDate = PatientCharge::create([
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
                    // dd($item);
                    PatientChargeLogs::create([
                        'enccode' => $enccode,
                        'acctno' => $acctno->paacctno,
                        'ward_stocks_id' => null,
                        'itemcode' => $item['itemCode'],
                        'manufactured_date' =>  null,
                        'delivery_date' => null,
                        'expiration_date' => null,
                        'quantity' => (int)$item['qtyToCharge'],
                        'price_per_piece' => (float)$item['price'],
                        'price_total' => (float)$item['qtyToCharge'] * (float)$item['price'],
                        'pcchrgdte' => $patientMiscChargeDate->pcchrgdte,
                        'tscode' => $request->tscode,
                        'entry_at' => $authWardcode->wardcode,
                        'entry_by' => $entryby,
                    ]);
                }

                // Drugs and Meds (Oxygen), carbon dioxide
                if ($item['typeOfCharge'] == 'DRUMD') {
                    $patientDrumDChargeDate = PatientCharge::create([
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
                        $wardStock = WardStocksTanks::where('itemcode', $item['itemCode'])
                            ->where('quantity', '!=', 0)
                            ->where('location', $authWardcode->wardcode)
                            ->orderBy('created_at', 'ASC')
                            ->first(); // 10

                        // execute if row selected qty is enough
                        if ($wardStock->quantity >= $remaining_qty_to_charge) {
                            PatientChargeLogs::create([
                                'enccode' => $enccode,
                                'acctno' => $acctno->paacctno,
                                'ward_stocks_id' => $wardStock->id,
                                'brand' => $wardStock->brand,
                                'itemcode' => $wardStock->itemcode,
                                'from' => $wardStock->from,
                                'manufactured_date' => $wardStock->manufactured_date == null ? null : $wardStock->manufactured_date,
                                'delivery_date' => $wardStock->delivery_date == null ? null : $wardStock->delivery_date,
                                'expiration_date' => $wardStock->expiration_date == null ? null : $wardStock->expiration_date,
                                'quantity' => $remaining_qty_to_charge,
                                'price_per_piece' => (float)$item['price'] == null ? null : (float)$item['price'],
                                'price_total' => (float)$remaining_qty_to_charge * (float)$item['price'],
                                // 'pcchrgdte' => $patientChargeDate->pcchrgdte,
                                'pcchrgdte' => $patientDrumDChargeDate->pcchrgdte,
                                'tscode' => $request->tscode,
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
                                'brand' => $wardStock->brand,
                                'itemcode' => $wardStock->itemcode,
                                'from' => $wardStock->from,
                                'manufactured_date' => $wardStock->manufactured_date == null ? null : $wardStock->manufactured_date,
                                'delivery_date' => $wardStock->delivery_date == null ? null : $wardStock->delivery_date,
                                'expiration_date' => $wardStock->expiration_date == null ? null : $wardStock->expiration_date,
                                'quantity' => $qty,
                                'price_per_piece' => (float)$item['price'] == null ? null : (float)$item['price'],
                                'price_total' => (float)$qty * (float)$item['price'],
                                // 'pcchrgdte' => $patientChargeDate->pcchrgdte,
                                'pcchrgdte' => $patientDrumDChargeDate->pcchrgdte,
                                'tscode' => $request->tscode,
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

                    // dd($item);
                    // PatientChargeLogs::create([
                    //     'enccode' => $enccode,
                    //     'acctno' => $acctno->paacctno,
                    //     'ward_stocks_id' => null,
                    //     'itemcode' => $item['itemCode'],
                    //     'manufactured_date' =>  null,
                    //     'delivery_date' => null,
                    //     'expiration_date' => null,
                    //     'quantity' => (int)$item['qtyToCharge'],
                    //     'price_per_piece' => (float)$item['price'],
                    //     'price_total' => (float)$item['qtyToCharge'] * (float)$item['price'],
                    //     'pcchrgdte' => $patientDrumDChargeDate->pcchrgdte,
                    //     'tscode' => $request->tscode,
                    //     'entry_at' => $authWardcode->wardcode,
                    //     'entry_by' => $entryby,
                    // ]);
                }

                // /Compressed Air
                if ($item['typeOfCharge'] == 'DRUMF') {
                    $patientDrumDChargeDate = PatientCharge::create([
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
                        $wardStock = WardStocksTanks::where('itemcode', $item['itemCode'])
                            ->where('quantity', '!=', 0)
                            ->where('location', $authWardcode->wardcode)
                            ->orderBy('created_at', 'ASC')
                            ->first(); // 10

                        // execute if row selected qty is enough
                        if ($wardStock->quantity >= $remaining_qty_to_charge) {
                            PatientChargeLogs::create([
                                'enccode' => $enccode,
                                'acctno' => $acctno->paacctno,
                                'ward_stocks_id' => $wardStock->id,
                                'brand' => $wardStock->brand,
                                'itemcode' => $wardStock->itemcode,
                                'from' => $wardStock->from,
                                'manufactured_date' => $wardStock->manufactured_date == null ? null : $wardStock->manufactured_date,
                                'delivery_date' => $wardStock->delivery_date == null ? null : $wardStock->delivery_date,
                                'expiration_date' => $wardStock->expiration_date == null ? null : $wardStock->expiration_date,
                                'quantity' => $remaining_qty_to_charge,
                                'price_per_piece' => (float)$item['price'] == null ? null : (float)$item['price'],
                                'price_total' => (float)$remaining_qty_to_charge * (float)$item['price'],
                                // 'pcchrgdte' => $patientChargeDate->pcchrgdte,
                                'pcchrgdte' => $patientDrumDChargeDate->pcchrgdte,
                                'tscode' => $request->tscode,
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
                                'brand' => $wardStock->brand,
                                'itemcode' => $wardStock->itemcode,
                                'from' => $wardStock->from,
                                'manufactured_date' => $wardStock->manufactured_date == null ? null : $wardStock->manufactured_date,
                                'delivery_date' => $wardStock->delivery_date == null ? null : $wardStock->delivery_date,
                                'expiration_date' => $wardStock->expiration_date == null ? null : $wardStock->expiration_date,
                                'quantity' => $qty,
                                'price_per_piece' => (float)$item['price'] == null ? null : (float)$item['price'],
                                'price_total' => (float)$qty * (float)$item['price'],
                                // 'pcchrgdte' => $patientChargeDate->pcchrgdte,
                                'pcchrgdte' => $patientDrumDChargeDate->pcchrgdte,
                                'tscode' => $request->tscode,
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

                    // dd($item);
                    // PatientChargeLogs::create([
                    //     'enccode' => $enccode,
                    //     'acctno' => $acctno->paacctno,
                    //     'ward_stocks_id' => null,
                    //     'itemcode' => $item['itemCode'],
                    //     'manufactured_date' =>  null,
                    //     'delivery_date' => null,
                    //     'expiration_date' => null,
                    //     'quantity' => (int)$item['qtyToCharge'],
                    //     'price_per_piece' => (float)$item['price'],
                    //     'price_total' => (float)$item['qtyToCharge'] * (float)$item['price'],
                    //     'pcchrgdte' => $patientDrumDChargeDate->pcchrgdte,
                    //     'tscode' => $request->tscode,
                    //     'entry_at' => $authWardcode->wardcode,
                    //     'entry_by' => $entryby,
                    // ]);
                }
            }
        } else {
            // dd($request);

            // dd($request);
            $previousCharge = null;
            $previousPatientChargeLogs = null;
            $previousWardStocks = null;
            $upd_QtyToReturn = ltrim($request->upd_QtyToReturn, '0'); // removed leading zeroes if theres any
            $authID = Auth::user()->employeeid;
            $authWard = $authWardcode->wardcode;

            // medical supplies
            if ($request->upd_type_of_charge_code == 'DRUMN') {
                $patientCharge = PatientCharge::where('enccode', $request->upd_enccode)
                    ->where('itemcode', $request->upd_itemcode)
                    ->where('pcchrgdte', $request->upd_pcchrgdte)
                    ->first();
                $previousCharge = $patientCharge;

                $patientChargeLogs = PatientChargeLogs::where('id', $request->upd_id)->first();
                $previousPatientChargeLogs = $patientChargeLogs;

                $wardStocks = WardsStocksMedSupp::where('id', $request->upd_ward_stocks_id)->first();
                $previousWardStocks = $wardStocks;

                // update the ward stock
                $wardStocks->update([
                    'quantity' => (int)$previousWardStocks->quantity + (int)$upd_QtyToReturn,
                ]);

                PatientChargeReturnLogs::create([
                    'enccode' => $request->enccode,
                    'location' => $authWard,
                    'hpercode' => $request->hospitalNumber,
                    'cl2comb' => $previousPatientChargeLogs->itemcode,
                    'returned_qty' => (int)$upd_QtyToReturn,
                    'entry_by' => Auth::user()->employeeid,
                ]);

                // delete the patient charge log
                $patientChargeLogs->delete();

                if ((int)$previousPatientChargeLogs->quantity != (int)$upd_QtyToReturn) {
                    PatientChargeLogs::create([
                        'enccode' => $previousPatientChargeLogs->enccode,
                        'acctno' => $previousPatientChargeLogs->acctno,
                        'ward_stocks_id' => $previousPatientChargeLogs->ward_stocks_id,
                        'brand' => $previousPatientChargeLogs->brand,
                        'itemcode' => $previousPatientChargeLogs->itemcode,
                        'from' => $previousPatientChargeLogs->from,
                        'manufactured_date' => $previousPatientChargeLogs->manufactured_date,
                        'delivery_date' => $previousPatientChargeLogs->delivery_date,
                        'expiration_date' => $previousPatientChargeLogs->expiration_date,
                        'quantity' => (int)$previousPatientChargeLogs->quantity - (int)$upd_QtyToReturn,
                        'price_per_piece' => $previousPatientChargeLogs->price_per_piece,
                        'price_total' => ((float)$previousPatientChargeLogs->quantity - (float)$upd_QtyToReturn) * (float)$previousPatientChargeLogs->price_per_piece,
                        'pcchrgdte' => $previousPatientChargeLogs->pcchrgdte,
                        'tscode' => $request->tscode,
                        'entry_at' => $authWard,
                        'entry_by' => $authID,
                        'created_at' => $previousPatientChargeLogs->created_at,
                        'updated_at' => $previousPatientChargeLogs->updated_at,
                    ]);
                }

                if (((int)$previousCharge->pchrgqty - (int)$upd_QtyToReturn) == 0) {
                    PatientCharge::where('enccode', $request->upd_enccode)
                        ->where('itemcode', $request->upd_itemcode)
                        ->where('pcchrgdte', $request->upd_pcchrgdte)
                        ->delete();
                } else {
                    PatientCharge::where('enccode', $request->upd_enccode)
                        ->where('itemcode', $request->upd_itemcode)
                        ->where('pcchrgdte', $request->upd_pcchrgdte)
                        ->update([
                            'enccode' => $previousCharge->enccode,
                            'hpercode' => $previousCharge->hpercode,
                            'upicode' => null,
                            'pcchrgcod' => $previousCharge->pcchrgcod, // charge slip no.
                            'pcchrgdte' => $previousCharge->pcchrgdte,
                            'chargcode' => $previousCharge->chargcode, // type of charge (chrgcode from hcharge)
                            'uomcode' => $previousCharge->uomcode, // unit
                            'pchrgqty' =>  (int)$previousCharge->pchrgqty - (int)$upd_QtyToReturn,
                            'pchrgup' => $previousCharge->pchrgup,
                            'pcchrgamt' => ((float)$previousCharge->pchrgqty - (float)$upd_QtyToReturn) * (float)$previousCharge->pchrgup,
                            'pcstat' => 'A', // always A
                            'pclock' => 'N', // always N
                            'updsw' => 'N', // always N
                            'confdl' => 'N', // always N
                            'srcchrg' => $previousCharge->srcchrg,
                            'pcdisch' => 'Y',
                            'acctno' => $previousCharge->acctno, // SELECT * FROM hpatacct --pacctno
                            'itemcode' => $previousCharge->itemcode, // cl2comb or hmisc hmcode
                            'entryby' => $authID,
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
                }
            }
            // misc
            else if ($request->upd_type_of_charge_code == 'MISC') {
                $patientCharge = PatientCharge::where('enccode', $request->upd_enccode)
                    ->where('itemcode', $request->upd_itemcode)
                    ->where('pcchrgdte', $request->upd_pcchrgdte)
                    ->first();
                $previousCharge = $patientCharge;

                $patientChargeLogs = PatientChargeLogs::where('id', $request->upd_id)->first();
                $previousPatientChargeLogs = $patientChargeLogs;

                // delete the patient charge log
                $patientChargeLogs->delete();

                if ((int)$previousPatientChargeLogs->quantity != (int)$upd_QtyToReturn) {
                    PatientChargeLogs::create([
                        'enccode' => $previousPatientChargeLogs->enccode,
                        'acctno' => $previousPatientChargeLogs->acctno,
                        'ward_stocks_id' => $previousPatientChargeLogs->ward_stocks_id,
                        'brand' => $previousPatientChargeLogs->brand,
                        'itemcode' => $previousPatientChargeLogs->itemcode,
                        'from' => $previousPatientChargeLogs->from,
                        'manufactured_date' => $previousPatientChargeLogs->manufactured_date,
                        'delivery_date' => $previousPatientChargeLogs->delivery_date,
                        'expiration_date' => $previousPatientChargeLogs->expiration_date,
                        'quantity' => (int)$previousPatientChargeLogs->quantity - (int)$upd_QtyToReturn,
                        'price_per_piece' => $previousPatientChargeLogs->price_per_piece,
                        'price_total' => ((float)$previousPatientChargeLogs->quantity - (float)$upd_QtyToReturn) * (float)$previousPatientChargeLogs->price_per_piece,
                        'pcchrgdte' => $previousPatientChargeLogs->pcchrgdte,
                        'tscode' => $request->tscode,
                        'entry_at' => $authWard,
                        'entry_by' => $authID,
                        'created_at' => $previousPatientChargeLogs->created_at,
                        'updated_at' => $previousPatientChargeLogs->updated_at,
                    ]);
                }

                if (((int)$previousCharge->pchrgqty - (int)$upd_QtyToReturn) == 0) {
                    PatientCharge::where('enccode', $request->upd_enccode)
                        ->where('itemcode', $request->upd_itemcode)
                        ->where('pcchrgdte', $request->upd_pcchrgdte)
                        ->delete();
                } else {
                    PatientCharge::where('enccode', $request->upd_enccode)
                        ->where('itemcode', $request->upd_itemcode)
                        ->where('pcchrgdte', $request->upd_pcchrgdte)
                        ->update([
                            'enccode' => $previousCharge->enccode,
                            'hpercode' => $previousCharge->hpercode,
                            'upicode' => null,
                            'pcchrgcod' => $previousCharge->pcchrgcod, // charge slip no.
                            'pcchrgdte' => $previousCharge->pcchrgdte,
                            'chargcode' => $previousCharge->chargcode, // type of charge (chrgcode from hcharge)
                            'uomcode' => $previousCharge->uomcode, // unit
                            'pchrgqty' =>  (int)$previousCharge->pchrgqty - (int)$upd_QtyToReturn,
                            'pchrgup' => $previousCharge->pchrgup,
                            'pcchrgamt' => ((float)$previousCharge->pchrgqty - (float)$upd_QtyToReturn) * (float)$previousCharge->pchrgup,
                            'pcstat' => 'A', // always A
                            'pclock' => 'N', // always N
                            'updsw' => 'N', // always N
                            'confdl' => 'N', // always N
                            'srcchrg' => $previousCharge->srcchrg,
                            'pcdisch' => 'Y',
                            'acctno' => $previousCharge->acctno, // SELECT * FROM hpatacct --pacctno
                            'itemcode' => $previousCharge->itemcode, // cl2comb or hmisc hmcode
                            'entryby' => $authID,
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
                }
            }
            // tanks (oxygen, compressed & carbon dioxide)
            else {
                $patientCharge = PatientCharge::where('enccode', $request->upd_enccode)
                    ->where('itemcode', $request->upd_itemcode)
                    ->where('pcchrgdte', $request->upd_pcchrgdte)
                    ->first();
                $previousCharge = $patientCharge;

                $patientChargeLogs = PatientChargeLogs::where('id', $request->upd_id)->first();
                $previousPatientChargeLogs = $patientChargeLogs;

                // delete the patient charge log
                $patientChargeLogs->delete();

                if ((int)$previousPatientChargeLogs->quantity != (int)$upd_QtyToReturn) {
                    PatientChargeLogs::create([
                        'enccode' => $previousPatientChargeLogs->enccode,
                        'acctno' => $previousPatientChargeLogs->acctno,
                        'ward_stocks_id' => $previousPatientChargeLogs->ward_stocks_id,
                        'brand' => $previousPatientChargeLogs->brand,
                        'itemcode' => $previousPatientChargeLogs->itemcode,
                        'from' => $previousPatientChargeLogs->from,
                        'manufactured_date' => $previousPatientChargeLogs->manufactured_date,
                        'delivery_date' => $previousPatientChargeLogs->delivery_date,
                        'expiration_date' => $previousPatientChargeLogs->expiration_date,
                        'quantity' => (int)$previousPatientChargeLogs->quantity - (int)$upd_QtyToReturn,
                        'price_per_piece' => $previousPatientChargeLogs->price_per_piece,
                        'price_total' => ((float)$previousPatientChargeLogs->quantity - (float)$upd_QtyToReturn) * (float)$previousPatientChargeLogs->price_per_piece,
                        'pcchrgdte' => $previousPatientChargeLogs->pcchrgdte,
                        'tscode' => $request->tscode,
                        'entry_at' => $authWard,
                        'entry_by' => $authID,
                        'created_at' => $previousPatientChargeLogs->created_at,
                        'updated_at' => $previousPatientChargeLogs->updated_at,
                    ]);
                }

                if (((int)$previousCharge->pchrgqty - (int)$upd_QtyToReturn) == 0) {
                    PatientCharge::where('enccode', $request->upd_enccode)
                        ->where('itemcode', $request->upd_itemcode)
                        ->where('pcchrgdte', $request->upd_pcchrgdte)
                        ->delete();
                } else {
                    PatientCharge::where('enccode', $request->upd_enccode)
                        ->where('itemcode', $request->upd_itemcode)
                        ->where('pcchrgdte', $request->upd_pcchrgdte)
                        ->update([
                            'enccode' => $previousCharge->enccode,
                            'hpercode' => $previousCharge->hpercode,
                            'upicode' => null,
                            'pcchrgcod' => $previousCharge->pcchrgcod, // charge slip no.
                            'pcchrgdte' => $previousCharge->pcchrgdte,
                            'chargcode' => $previousCharge->chargcode, // type of charge (chrgcode from hcharge)
                            'uomcode' => $previousCharge->uomcode, // unit
                            'pchrgqty' =>  (int)$previousCharge->pchrgqty - (int)$upd_QtyToReturn,
                            'pchrgup' => $previousCharge->pchrgup,
                            'pcchrgamt' => ((float)$previousCharge->pchrgqty - (float)$upd_QtyToReturn) * (float)$previousCharge->pchrgup,
                            'pcstat' => 'A', // always A
                            'pclock' => 'N', // always N
                            'updsw' => 'N', // always N
                            'confdl' => 'N', // always N
                            'srcchrg' => $previousCharge->srcchrg,
                            'pcdisch' => 'Y',
                            'acctno' => $previousCharge->acctno, // SELECT * FROM hpatacct --pacctno
                            'itemcode' => $previousCharge->itemcode, // cl2comb or hmisc hmcode
                            'entryby' => $authID,
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
                }
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
