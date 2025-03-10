<?php

namespace App\Http\Controllers\Wards\Patients;

use App\Events\ChargeLogsProcessed;
use App\Events\RequestStock;
use App\Http\Controllers\Controller;
use App\Jobs\CreatePatientChargeLogsJobs;
use App\Models\AdmissionLog;
use App\Models\CsrwCode;
use App\Models\ERlog;
use App\Models\Item;
use App\Models\LocationStockBalance;
use App\Models\Miscellaneous;
use App\Models\Opdlog;
use App\Models\Patient;
use App\Models\PatientAccount;
use App\Models\PatientCharge;
use App\Models\PatientChargeLogs;
use App\Models\PatientChargeReturnLogs;
use App\Models\TypeOfCharge;
use App\Models\WardsStocks;
use App\Rules\StockBalanceNotDeclaredYetRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Models\Sessions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use PhpParser\Node\Stmt\TryCatch;
use stdClass;

class PatientChargeController extends Controller
{
    public function index(Request $request)
    {
        $pat_enccode = $request->enccode;
        $hpercode = $request->hpercode;
        $patient_name = $request->patient;
        $is_for_discharge = $request->disch;
        $room_bed = $request->room_bed;

        // NEW
        $pat_tscode = '';
        $admlog_tscode = AdmissionLog::where('enccode', $pat_enccode)->get('tscode')->first();
        $opdlog_tscode = Opdlog::where('enccode', $pat_enccode)->get('tscode')->first();
        $erlog_tscode = ERlog::where('enccode', $pat_enccode)->get('tscode')->first();
        // enccode is 1 is to 1
        $pat_tscode = $admlog_tscode->tscode ?? $opdlog_tscode->tscode ?? $erlog_tscode->tscode ?? null;
        $medicalSupplies = array();

        $authWardCode_cached = Cache::get('c_authWardCode_' . Auth::user()->employeeid);
        $wardcode = $authWardCode_cached;

        // this query will show stocks that have the received status but also get the status FROM MEDICAL GASES and EXISTING_STOCKS
        $stocksFromCsr = DB::select(
            "SELECT
                csrw_wards_stocks.[from],
                csrw_wards_stocks.id,
                csrw_wards_stocks.request_stocks_id,
                csrw_wards_stocks.is_consumable,
                item.cl2comb,
                item.cl2desc,
                item.uomcode,
                csrw_wards_stocks.quantity,
                csrw_wards_stocks.average,
                csrw_wards_stocks.total_usage,
                price.price_per_unit AS price,
                csrw_wards_stocks.expiration_date
            FROM csrw_wards_stocks
            JOIN hclass2 AS item ON item.cl2comb = csrw_wards_stocks.cl2comb
            JOIN csrw_item_prices AS price ON csrw_wards_stocks.cl2comb = price.cl2comb
            LEFT JOIN csrw_request_stocks AS request ON csrw_wards_stocks.request_stocks_id = request.id
            WHERE csrw_wards_stocks.location = '" . $wardcode . "'
                AND csrw_wards_stocks.ris_no = price.ris_no
                AND csrw_wards_stocks.quantity > 0
                AND csrw_wards_stocks.expiration_date > GETDATE()
                AND (
                    (request.status = 'RECEIVED') -- Include items with a valid request_stocks_id and status RECEIVED
                    OR (csrw_wards_stocks.request_stocks_id IS NULL AND csrw_wards_stocks.[from] IN ('MEDICAL GASES', 'EXISTING_STOCKS', 'CONSIGNMENT')) -- Include items that is not from CSR
                )
            ORDER BY csrw_wards_stocks.expiration_date ASC;"
        );

        // set medicalSupplies value and remove duplicate id
        $medicalSupplies = [];
        $seenIds = [];

        foreach ($stocksFromCsr as $s) {
            if (!in_array($s->id, $seenIds)) {
                $medicalSupplies[] = (object) [
                    'from' => $s->from,
                    'id' => $s->id,
                    'is_consumable' => $s->is_consumable,
                    'cl2comb' => $s->cl2comb,
                    'cl2desc' => $s->cl2desc,
                    'uomcode' => $s->uomcode,
                    'quantity' => $s->quantity,
                    'average' => $s->average,
                    'total_usage' => $s->total_usage,
                    'price' => $s->price,
                    'expiration_date' => $s->expiration_date,
                ];
                $seenIds[] = $s->id;
            }
        }

        // get miscellaneous / miscellaneous
        $misc = Miscellaneous::with('unit')
            ->where('hmstat', 'A')
            ->get(['hmcode', 'hmdesc', 'hmamt', 'uomcode']);

        $bills = DB::select(
            "SELECT pat_charge.pcchrgcod as charge_slip_no,
                            type_of_charge.chrgcode as type_of_charge_code,
                            type_of_charge.chrgdesc as type_of_charge_description,
                            category.cl1desc as category,
                            item.cl2desc as item,
                            misc.hmdesc as misc,
                            pat_charge.itemcode as itemcode,
                            pat_charge.pchrgqty as quantity,
                            pat_charge.pchrgup as price,
                            pat_charge.uomcode as uomcode,
                            pat_charge.pcchrgdte as charge_date,
                            charge_log.id as charge_log_id,
                            charge_log.[from] as charge_log_from,
                            charge_log.ward_stocks_id as charge_log_ward_stocks_id,
                            charge_log.quantity as charge_log_quantity,
                            charge_log.expiration_date as charge_log_expiration_date,
                            charge_by.firstname + ' ' + charge_by.lastname as entry_by
                            FROM hospital.dbo.hpatchrg pat_charge
                            LEFT JOIN hospital.dbo.hclass2 as item ON pat_charge.itemcode = item.cl2comb
                            LEFT JOIN hospital.dbo.hclass1 as category ON item.cl1comb = category.cl1comb
                            LEFT JOIN hospital.dbo.hmisc as misc ON pat_charge.itemcode = misc.hmcode
                            LEFT JOIN hospital.dbo.hpersonal as charge_by ON pat_charge.entryby = charge_by.employeeid
                            LEFT JOIN hospital.dbo.hcharge as type_of_charge ON pat_charge.chargcode = type_of_charge.chrgcode
                            LEFT JOIN hospital.dbo.csrw_patient_charge_logs as charge_log ON pat_charge.enccode = charge_log.enccode
                                                                                        AND pat_charge.pcchrgdte = charge_log.pcchrgdte
                                                                                        AND pat_charge.itemcode = charge_log.itemcode
                            WHERE pat_charge.enccode = '" . $pat_enccode . "'
                            AND  (type_of_charge.chrgcode = 'DRUMD' OR type_of_charge.chrgcode = 'DRUMN' OR type_of_charge.chrgcode = 'MISC')
                            ORDER BY pat_charge.pcchrgdte DESC"
        );

        // check if the latest has a beg bal or ending bal
        $balanceDecChecker = LocationStockBalance::where('location', $wardcode)->OrderBy('created_at', 'DESC')->first();
        $canCharge = null;

        if ($balanceDecChecker == null) {
            $canCharge = false;
        } else if ($balanceDecChecker['beg_bal_created_at'] !== null) {
            $canCharge = true;
        } else if ($balanceDecChecker['beg_bal_created_at'] == null) {
            $canCharge = false;
        }


        return Inertia::render('Wards/Patients/Bill/Index', [
            // 'pat_name' => $pat_name,
            'hpercode' => $hpercode,
            'patient_name' => $patient_name,
            'pat_tscode' => $pat_tscode,
            'pat_enccode' => $pat_enccode,
            'room_bed' => $room_bed,
            // 'patient' => $patient,
            'bills' => $bills,
            'medicalSupplies' => $medicalSupplies,
            'misc' => $misc,
            'is_for_discharge' => $is_for_discharge,
            'canCharge' => $canCharge,
        ]);
    }

    protected function generateUniqueChargeCode()
    {
        $csrw_code = CsrwCode::create([
            'charge_desc' => 'a',
            'created_at' => Carbon::now(),
        ]);
        // get count of csrcode where year is NOW
        $currentCodeCount = CsrwCode::whereYear('created_at', Carbon::now()->year)->count();
        // CW = Central supply room Ward
        $pcchrgcod = 'CW' . Carbon::now()->format('y') . '-' . sprintf('%06d', $currentCodeCount);

        return $pcchrgcod;
    }

    public function store(Request $request)
    {
        // return $request;
        $data = $request;

        $entryby = Auth::user()->employeeid;

        $srcchrg = 'WARD';

        $authWardCode_cached = Cache::get('c_authWardCode_' . Auth::user()->employeeid);
        $authCode = $authWardCode_cached;

        $enccode = $request->enccode;
        $hospitalNumber = $request->hospitalNumber;
        $itemsToBillList = $request->itemsToBillList;

        // sort the items by itemDesc
        usort($itemsToBillList, function ($a, $b) {
            return strcmp($a["itemDesc"], $b["itemDesc"]); // Ascending order
        });

        $pcchrgcod = $this->generateUniqueChargeCode();
        $processedItems = []; // Store processed item codes with their pcchrgcod


        // STEP 1: check if the request is a new charge or modifying a charge
        if ($request->isUpdate == false) {
            // STEP 2: check patient account
            $r = PatientAccount::where('enccode', $enccode)->first(['paacctno']);
            $acctno = $r != null ? $r->paacctno : '';

            // STEP 3: Loop through the items to charge
            foreach ($itemsToBillList as $item) {
                // dd($item);
                // init tscode
                $tscode = $request->tscode;

                // STEP 3.1: Check if itemCOde is already charge with the same pcchrgcod
                if (!isset($processedItems[$item['itemCode']])) {
                    // Assign the main pcchrgcod to the first occurrence of an itemCode
                    $processedItems[$item['itemCode']] = $pcchrgcod;
                } else {
                    // If the itemCode has already been used, generate a new charge code for duplicates
                    $processedItems[$item['itemCode']] = $this->generateUniqueChargeCode();
                }

                // STEP 4: Charge the item to a patient
                // try..catch block will prevent further execution if
                // the code inside failed. Meaning, deductiong stock and logging also fails
                try {
                    $patientChargeDate = PatientCharge::create([
                        'enccode' => $enccode,
                        'hpercode' => $hospitalNumber,
                        'pcchrgcod' => $processedItems[$item['itemCode']],
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
                        'acctno' => $acctno, // SELECT * FROM hpatacct --pacctno
                        'itemcode' => $item['itemCode'], // cl2comb or hmisc hmcode
                        'entryby' => $entryby,
                    ]);
                } catch (\Throwable $th) {
                    throw $th;
                }

                $quantity_to_insert_in_logs = $item['qtyToCharge'];

                // STEP 5: Check if items is a medical supply or misc
                if ($item['typeOfCharge'] == 'DRUMN') {
                    // declare new stock qty variable
                    $newStockQty = 0;

                    // STEP 6: remove quantity based on ward stock item id
                    $wardStock = WardsStocks::where('cl2comb', $item['itemCode'])
                        ->where('location', $authCode)
                        ->where('id', $item['id'])
                        ->first(); // 10

                    // Since medical gas is also considereded as medical supplies.
                    // STEP 7: check if the item is regular medical supplies or a medical gas
                    if ($wardStock->is_consumable != 'y') {
                        // save the quantity because the value of $item['qtyToCharge'] will
                        // change below
                        // $quantity_to_insert_in_logs = $item['qtyToCharge'];

                        // STEP 8: updating the stocks
                        // getting the new qty of current editing ward stock
                        $newStockQty = $wardStock->quantity - $item['qtyToCharge'];
                        // setting the new value of remaining_qty_to_charge
                        $item['qtyToCharge'] = $item['qtyToCharge'] - $wardStock->quantity;

                        $wardStock::where('id', $wardStock->id)
                            ->update([
                                'quantity' => $newStockQty,
                            ]);
                        // dd($wardStock->id);

                        // parameters of the job
                        $enccode = $enccode;
                        $acctno = $acctno;
                        $ward_stocks_id = $wardStock->id;
                        $itemcode = $wardStock->cl2comb;
                        $from = $wardStock->from;
                        $manufactured_date = $wardStock->manufactured_date;
                        $delivery_date = $wardStock->delivery_date;
                        $expiration_date = $wardStock->expiration_date;
                        $quantity = $quantity_to_insert_in_logs;
                        $price_per_piece = (float)$item['price'] == null ? null : (float)$item['price'];
                        $price_total = (float)$quantity_to_insert_in_logs * (float)$item['price'];
                        $pcchrgdte = $patientChargeDate->pcchrgdte;
                        $entry_at = $authCode;
                        $entry_by = $entryby;
                        $pcchrgcod = $patientChargeDate->pcchrgcod;

                        // STEP 9: Log the charge in a JOBS
                        CreatePatientChargeLogsJobs::dispatch(
                            $enccode,
                            $acctno,
                            $ward_stocks_id,
                            $itemcode,
                            $from,
                            $manufactured_date,
                            $delivery_date,
                            $expiration_date,
                            $quantity,
                            $price_per_piece,
                            $price_total,
                            $pcchrgdte, // charge date
                            $entry_at,
                            $entry_by,
                            $tscode,
                            $pcchrgcod
                        );

                        // // STEP 9: Log the charge
                        // PatientChargeLogs::create([
                        //     'enccode' => $enccode,
                        //     'acctno' => $acctno,
                        //     'ward_stocks_id' => $wardStock->id,
                        //     'itemcode' => $wardStock->cl2comb,
                        //     'from' => $wardStock->from,
                        //     'manufactured_date' => $wardStock->manufactured_date == null ? null : Carbon::parse($wardStock->manufactured_date)->format('Y-m-d H:i:s.v'),
                        //     'delivery_date' => $wardStock->delivery_date == null ? null : Carbon::parse($wardStock->delivered_date)->format('Y-m-d H:i:s.v'),
                        //     'expiration_date' => $wardStock->expiration_date == null ? null : Carbon::parse($wardStock->expiration_date)->format('Y-m-d H:i:s.v'),
                        //     // 'quantity' => $item['qtyToCharge'],
                        //     'quantity' => $quantity_to_insert_in_logs,
                        //     'price_per_piece' => (float)$item['price'] == null ? null : (float)$item['price'],
                        //     'price_total' => (float)$quantity_to_insert_in_logs * (float)$item['price'],
                        //     'pcchrgdte' => $patientChargeDate->pcchrgdte,
                        //     'tscode' => $request->tscode,
                        //     'entry_at' => $authCode,
                        //     'entry_by' => $entryby,
                        //     'pcchrgcod' => $patientChargeDate->pcchrgcod, // charge slip no.
                        // ]);
                    }
                    // IT ITEM IS MEDICAL GAS
                    else {
                        // save the quantity because the value of $item['qtyToCharge'] will
                        // change below
                        // $quantity_to_insert_in_logs = $item['qtyToCharge'];

                        // Calculate the new total_usage after charging the patient
                        $newTotalUsage = $wardStock->total_usage - $item['qtyToCharge'];

                        // Calculate the number of full tanks left
                        $fullTanks = (int) floor($newTotalUsage / $wardStock->average);

                        // Determine if there's a partial tank left
                        $remainingInLastTank = $newTotalUsage % $wardStock->average;

                        // Update the quantity: full tanks + 1 if there's a partial tank
                        $newQuantity = $fullTanks + ($remainingInLastTank > 0 ? 1 : 0);

                        // Update the ward stock with the new total_usage and quantity
                        $wardStock::where('id', $wardStock->id)
                            ->update([
                                'total_usage' => $newTotalUsage,
                                'quantity' => $newQuantity,
                            ]);

                        // parameters of the job
                        $enccode = $enccode;
                        $acctno = $acctno;
                        $ward_stocks_id = $wardStock->id;
                        $itemcode = $wardStock->cl2comb;
                        $from = $wardStock->from;
                        $manufactured_date = $wardStock->manufactured_date;
                        $delivery_date = $wardStock->delivery_date;
                        $expiration_date = $wardStock->expiration_date;
                        $quantity = $quantity_to_insert_in_logs;
                        $price_per_piece = (float)$item['price'] == null ? null : (float)$item['price'];
                        $price_total = (float)$quantity_to_insert_in_logs * (float)$item['price'];
                        $pcchrgdte = $patientChargeDate->pcchrgdte;
                        $entry_at = $authCode;
                        $entry_by = $entryby;
                        $pcchrgcod = $patientChargeDate->pcchrgcod;

                        // Log the charge
                        CreatePatientChargeLogsJobs::dispatch(
                            $enccode,
                            $acctno,
                            $ward_stocks_id,
                            $itemcode,
                            $from,
                            $manufactured_date,
                            $delivery_date,
                            $expiration_date,
                            $quantity,
                            $price_per_piece,
                            $price_total,
                            $pcchrgdte, // charge date
                            $entry_at,
                            $entry_by,
                            $tscode,
                            $pcchrgcod
                        );

                        // Log the charge
                        // PatientChargeLogs::create([
                        //     'enccode' => $enccode,
                        //     'acctno' => $acctno,
                        //     'ward_stocks_id' => $wardStock->id,
                        //     'itemcode' => $wardStock->cl2comb,
                        //     'from' => $wardStock->from,
                        //     'manufactured_date' => $wardStock->manufactured_date == null ? null : Carbon::parse($wardStock->manufactured_date)->format('Y-m-d H:i:s.v'),
                        //     'delivery_date' => $wardStock->delivery_date == null ? null : Carbon::parse($wardStock->delivered_date)->format('Y-m-d H:i:s.v'),
                        //     'expiration_date' => $wardStock->expiration_date == null ? null : Carbon::parse($wardStock->expiration_date)->format('Y-m-d H:i:s.v'),
                        //     'quantity' => $quantity_to_insert_in_logs,
                        //     'price_per_piece' => (float)$item['price'] == null ? null : (float)$item['price'],
                        //     'price_total' => (float)$quantity_to_insert_in_logs * (float)$item['price'],
                        //     'pcchrgdte' => $patientChargeDate->pcchrgdte,
                        //     'tscode' => $request->tscode,
                        //     'entry_at' => $authCode,
                        //     'entry_by' => $entryby,
                        //     'pcchrgcod' => $patientChargeDate->pcchrgcod, // charge slip no.
                        // ]);
                    }
                }

                // MISC
                if ($item['typeOfCharge'] == 'MISC') {
                    // $quantity_to_insert_in_logs = $item['qtyToCharge'];

                    // // try..catch block will prevent further execution if
                    // // the code inside failed. Meaning logging also fails
                    // try {
                    //     $patientChargeDate = PatientCharge::create([
                    //         'enccode' => $enccode,
                    //         'hpercode' => $hospitalNumber,
                    //         'pcchrgcod' => $processedItems[$item['itemCode']],
                    //         'pcchrgdte' => Carbon::now(),
                    //         'chargcode' => $item['typeOfCharge'], // type of charge (chrgcode from hcharge)
                    //         'uomcode' => $item['unit'], // unit
                    //         'pchrgqty' =>  $item['qtyToCharge'],
                    //         'pchrgup' => $item['price'],
                    //         'pcchrgamt' => $item['total'],
                    //         'pcstat' => 'A', // always A
                    //         'pclock' => 'N', // always N
                    //         'updsw' => 'N', // always N
                    //         'confdl' => 'N', // always N
                    //         'srcchrg' => $srcchrg,
                    //         'pcdisch' => 'Y',
                    //         'acctno' => $acctno, // SELECT * FROM hpatacct --pacctno
                    //         'itemcode' => $item['itemCode'], // cl2comb or hmisc hmcode
                    //         'entryby' => $entryby,
                    //     ]);
                    // } catch (\Throwable $th) {
                    //     throw $th;
                    // }

                    // parameters of the job
                    $enccode = $enccode;
                    $acctno = $acctno;
                    $ward_stocks_id = NULL;
                    $itemcode = $item['itemCode'];
                    $from = NULL;
                    $manufactured_date = NULL;
                    $delivery_date = NULL;
                    $expiration_date = NULL;
                    $quantity = $quantity_to_insert_in_logs;
                    $price_per_piece = (float)$item['price'] == null ? null : (float)$item['price'];
                    $price_total = (float)$quantity_to_insert_in_logs * (float)$item['price'];
                    $pcchrgdte = $patientChargeDate->pcchrgdte;
                    $entry_at = $authCode;
                    $entry_by = $entryby;
                    $pcchrgcod = $patientChargeDate->pcchrgcod;

                    // Log the charge
                    CreatePatientChargeLogsJobs::dispatch(
                        $enccode,
                        $acctno,
                        $ward_stocks_id,
                        $itemcode,
                        $from,
                        $manufactured_date,
                        $delivery_date,
                        $expiration_date,
                        $quantity,
                        $price_per_piece,
                        $price_total,
                        $pcchrgdte, // charge date
                        $entry_at,
                        $entry_by,
                        $tscode,
                        $pcchrgcod
                    );

                    // log the charge
                    //     PatientChargeLogs::create([
                    //         'enccode' => $enccode,
                    //         'acctno' => $acctno,
                    //         'ward_stocks_id' => null,
                    //         'itemcode' => $item['itemCode'],
                    //         'manufactured_date' =>  null,
                    //         'delivery_date' => null,
                    //         'expiration_date' => null,
                    //         'quantity' => (int)$item['qtyToCharge'],
                    //         'price_per_piece' => (float)$item['price'],
                    //         'price_total' => (float)$item['qtyToCharge'] * (float)$item['price'],
                    //         'pcchrgdte' => $patientMiscChargeDate->pcchrgdte,
                    //         'tscode' => $request->tscode,
                    //         'entry_at' => $authCode,
                    //         'entry_by' => $entryby,
                    //         'pcchrgcod' => $patientMiscChargeDate->pcchrgcod, // charge slip no.
                    //     ]);
                }
            }
        }

        // if returning a charge
        if ($request->isUpdate == true) {
            // void/return MISC item
            if ($request->upd_type_of_charge_code == 'MISC') {
                $previousCharge = null;
                $previousPatientChargeLogs = null;
                $previousWardStocks = null;
                $upd_QtyToReturn = ltrim($request->upd_QtyToReturn, '0'); // removed leading zeroes if theres any
                $authID = Auth::user()->employeeid;
                $authWard = $authCode;

                $patientCharge = PatientCharge::where('enccode', $request->upd_enccode)
                    ->where('itemcode', $request->upd_itemcode)
                    ->where('pcchrgdte', $request->upd_pcchrgdte)
                    ->first();
                $previousCharge = $patientCharge;
                // dd($request);

                $patientChargeLogs = PatientChargeLogs::where('id', $request->upd_id)->first();
                $previousPatientChargeLogs = $patientChargeLogs;

                // delete the patient charge log
                $patientChargeLogs->delete();

                // check if it only updates the quantity of charged items
                if ((int)$previousPatientChargeLogs->quantity != (int)$upd_QtyToReturn) {
                    PatientChargeLogs::create([
                        'enccode' => $previousPatientChargeLogs->enccode,
                        'acctno' => $previousPatientChargeLogs->acctno,
                        'ward_stocks_id' => $previousPatientChargeLogs->ward_stocks_id,
                        'itemcode' => $previousPatientChargeLogs->itemcode,
                        'from' => $previousPatientChargeLogs->from,
                        'manufactured_date' => Carbon::parse($previousPatientChargeLogs->manufactured_date)->format('Y-m-d H:i:s.v'),
                        'delivery_date' => Carbon::parse($previousPatientChargeLogs->delivered_date)->format('Y-m-d H:i:s.v'),
                        'expiration_date' => Carbon::parse($previousPatientChargeLogs->expiration_date)->format('Y-m-d H:i:s.v'),
                        'quantity' => (int)$previousPatientChargeLogs->quantity - (int)$upd_QtyToReturn,
                        'price_per_piece' => $previousPatientChargeLogs->price_per_piece,
                        'price_total' => ((float)$previousPatientChargeLogs->quantity - (float)$upd_QtyToReturn) * (float)$previousPatientChargeLogs->price_per_piece,
                        'pcchrgdte' => $previousPatientChargeLogs->pcchrgdte,
                        'tscode' => $request->tscode,
                        'entry_at' => $authWard,
                        'entry_by' => $authID,
                        'created_at' => $previousPatientChargeLogs->created_at,
                        'updated_at' => $previousPatientChargeLogs->updated_at,
                        'pcchrgcod' => $previousPatientChargeLogs->pcchrgcod, // charge slip no.
                    ]);
                }

                // if previous charge minus the quantity to return = 0 then void the charge (delete charge)
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
            } else {
                $stock = WardsStocks::where('id', $request->upd_ward_stocks_id)->first();

                // RETURN NON-CONSUMABLE ITEMS
                if ($request->charge_log_from != 'MEDICAL GASES' && $stock->is_consumable == null) {
                    // dd('return');
                    $previousCharge = null;
                    $previousPatientChargeLogs = null;
                    $previousWardStocks = null;
                    $upd_QtyToReturn = ltrim($request->upd_QtyToReturn, '0'); // removed leading zeroes if theres any
                    $authID = Auth::user()->employeeid;
                    $authWard = $authCode;

                    // medical supplies
                    if ($request->upd_type_of_charge_code == 'DRUMN') {
                        $patientCharge = PatientCharge::where('enccode', $request->upd_enccode)
                            ->where('itemcode', $request->upd_itemcode)
                            ->where('pcchrgdte', $request->upd_pcchrgdte)
                            ->first();
                        $previousCharge = $patientCharge;

                        $patientChargeLogs = PatientChargeLogs::where('id', $request->upd_id)->first();
                        $previousPatientChargeLogs = $patientChargeLogs;

                        $wardStocks = WardsStocks::where('id', $request->upd_ward_stocks_id)->first();
                        $previousWardStocks = $wardStocks;

                        // update the ward stock
                        $wardStocks->update([
                            'quantity' => (int)$previousWardStocks->quantity + (int)$upd_QtyToReturn,
                        ]);

                        PatientChargeReturnLogs::create([
                            'enccode' => $request->enccode,
                            'location' => $authWard,
                            'hpercode' => $request->hospitalNumber,
                            'itemcode' => $previousPatientChargeLogs->itemcode,
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
                                'itemcode' => $previousPatientChargeLogs->itemcode,
                                'from' => $previousPatientChargeLogs->from,
                                'manufactured_date' => Carbon::parse($previousPatientChargeLogs->manufactured_date)->format('Y-m-d H:i:s.v'),
                                'delivery_date' => Carbon::parse($previousPatientChargeLogs->delivered_date)->format('Y-m-d H:i:s.v'),
                                'expiration_date' => Carbon::parse($previousPatientChargeLogs->expiration_date)->format('Y-m-d H:i:s.v'),
                                'quantity' => (int)$previousPatientChargeLogs->quantity - (int)$upd_QtyToReturn,
                                'price_per_piece' => $previousPatientChargeLogs->price_per_piece,
                                'price_total' => ((float)$previousPatientChargeLogs->quantity - (float)$upd_QtyToReturn) * (float)$previousPatientChargeLogs->price_per_piece,
                                'pcchrgdte' => $previousPatientChargeLogs->pcchrgdte,
                                'tscode' => $request->tscode,
                                'entry_at' => $authWard,
                                'entry_by' => $authID,
                                'created_at' => $previousPatientChargeLogs->created_at,
                                'updated_at' => $previousPatientChargeLogs->updated_at,
                                'pcchrgcod' => $previousPatientChargeLogs->pcchrgcod, // charge slip no.
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
                    // else {
                    //     $patientCharge = PatientCharge::where('enccode', $request->upd_enccode)
                    //         ->where('itemcode', $request->upd_itemcode)
                    //         ->where('pcchrgdte', $request->upd_pcchrgdte)
                    //         ->first();
                    //     $previousCharge = $patientCharge;

                    //     $patientChargeLogs = PatientChargeLogs::where('id', $request->upd_id)->first();
                    //     $previousPatientChargeLogs = $patientChargeLogs;

                    //     // delete the patient charge log
                    //     $patientChargeLogs->delete();

                    //     if ((int)$previousPatientChargeLogs->quantity != (int)$upd_QtyToReturn) {
                    //         PatientChargeLogs::create([
                    //             'enccode' => $previousPatientChargeLogs->enccode,
                    //             'acctno' => $previousPatientChargeLogs->acctno,
                    //             'ward_stocks_id' => $previousPatientChargeLogs->ward_stocks_id,
                    //             'itemcode' => $previousPatientChargeLogs->itemcode,
                    //             'from' => $previousPatientChargeLogs->from,
                    //             'manufactured_date' => Carbon::parse($previousPatientChargeLogs->manufactured_date)->format('Y-m-d H:i:s.v'),
                    //             'delivery_date' => Carbon::parse($previousPatientChargeLogs->delivered_date)->format('Y-m-d H:i:s.v'),
                    //             'expiration_date' => Carbon::parse($previousPatientChargeLogs->expiration_date)->format('Y-m-d H:i:s.v'),
                    //             'quantity' => (int)$previousPatientChargeLogs->quantity - (int)$upd_QtyToReturn,
                    //             'price_per_piece' => $previousPatientChargeLogs->price_per_piece,
                    //             'price_total' => ((float)$previousPatientChargeLogs->quantity - (float)$upd_QtyToReturn) * (float)$previousPatientChargeLogs->price_per_piece,
                    //             'pcchrgdte' => $previousPatientChargeLogs->pcchrgdte,
                    //             'tscode' => $request->tscode,
                    //             'entry_at' => $authWard,
                    //             'entry_by' => $authID,
                    //             'created_at' => $previousPatientChargeLogs->created_at,
                    //             'updated_at' => $previousPatientChargeLogs->updated_at,
                    //         ]);
                    //     }

                    //     if (((int)$previousCharge->pchrgqty - (int)$upd_QtyToReturn) == 0) {
                    //         PatientCharge::where('enccode', $request->upd_enccode)
                    //             ->where('itemcode', $request->upd_itemcode)
                    //             ->where('pcchrgdte', $request->upd_pcchrgdte)
                    //             ->delete();
                    //     } else {
                    //         PatientCharge::where('enccode', $request->upd_enccode)
                    //             ->where('itemcode', $request->upd_itemcode)
                    //             ->where('pcchrgdte', $request->upd_pcchrgdte)
                    //             ->update([
                    //                 'enccode' => $previousCharge->enccode,
                    //                 'hpercode' => $previousCharge->hpercode,
                    //                 'upicode' => null,
                    //                 'pcchrgcod' => $previousCharge->pcchrgcod, // charge slip no.
                    //                 'pcchrgdte' => $previousCharge->pcchrgdte,
                    //                 'chargcode' => $previousCharge->chargcode, // type of charge (chrgcode from hcharge)
                    //                 'uomcode' => $previousCharge->uomcode, // unit
                    //                 'pchrgqty' =>  (int)$previousCharge->pchrgqty - (int)$upd_QtyToReturn,
                    //                 'pchrgup' => $previousCharge->pchrgup,
                    //                 'pcchrgamt' => ((float)$previousCharge->pchrgqty - (float)$upd_QtyToReturn) * (float)$previousCharge->pchrgup,
                    //                 'pcstat' => 'A', // always A
                    //                 'pclock' => 'N', // always N
                    //                 'updsw' => 'N', // always N
                    //                 'confdl' => 'N', // always N
                    //                 'srcchrg' => $previousCharge->srcchrg,
                    //                 'pcdisch' => 'Y',
                    //                 'acctno' => $previousCharge->acctno, // SELECT * FROM hpatacct --pacctno
                    //                 'itemcode' => $previousCharge->itemcode, // cl2comb or hmisc hmcode
                    //                 'entryby' => $authID,
                    //                 'orinclst' => null, // null
                    //                 'compense' => null, // always null
                    //                 'proccode' => null, // always null
                    //                 'discount' => null, // always null
                    //                 'disamt' => null, // always null
                    //                 'discbal' => null, // always null
                    //                 'phicamt' => null, // always null
                    //                 'rvscode' => null, // always null
                    //                 'licno' => null, // always null
                    //                 'hpatkey' => null, // always null
                    //                 'time_frequency' => null, // always null
                    //                 'unit_frequency' => null, // always null
                    //                 'qtyintake' => null, // always null
                    //                 'uomintake' => null, // always null
                    //             ]);
                    //     }
                    // }
                } else {
                    // RETURN CONSUMABLE ITEMS
                    // dd($request);
                    $previousCharge = null;
                    $previousPatientChargeLogs = null;
                    $previousWardStocks = null;
                    $upd_QtyToReturn = ltrim($request->upd_QtyToReturn, '0'); // removed leading zeroes if there's any
                    $authID = Auth::user()->employeeid;
                    $authWard = $authCode;

                    // medical supplies
                    if ($request->upd_type_of_charge_code == 'DRUMN') {
                        $patientCharge = PatientCharge::where('enccode', $request->upd_enccode)
                            ->where('itemcode', $request->upd_itemcode)
                            ->where(
                                'pcchrgdte',
                                $request->upd_pcchrgdte
                            )
                            ->first();
                        $previousCharge = $patientCharge;

                        $patientChargeLogs = PatientChargeLogs::where('id', $request->upd_id)->first();
                        $previousPatientChargeLogs = $patientChargeLogs;

                        $wardStocks = WardsStocks::where('id', $request->upd_ward_stocks_id)->first();
                        $previousWardStocks = $wardStocks;

                        // Update the ward stock total_usage
                        $newTotalUsage = (int)$previousWardStocks->total_usage + (int)$upd_QtyToReturn;

                        // Calculate the number of full tanks left
                        $fullTanks = (int) floor($newTotalUsage / $wardStocks->average);

                        // Determine if there's a partial tank left
                        $remainingInLastTank = $newTotalUsage % $wardStocks->average;

                        // Update the quantity: full tanks + 1 if there's a partial tank
                        $newQuantity = $fullTanks + ($remainingInLastTank > 0 ? 1 : 0);

                        // Update the ward stock with the new total_usage and quantity
                        $wardStocks->update([
                            'total_usage' => $newTotalUsage,
                            'quantity' => $newQuantity,
                        ]);

                        // Log the return
                        PatientChargeReturnLogs::create([
                            'enccode' => $request->enccode,
                            'location' => $authWard,
                            'hpercode' => $request->hospitalNumber,
                            'itemcode' => $previousPatientChargeLogs->itemcode,
                            'returned_qty' => (int)$upd_QtyToReturn,
                            'entry_by' => Auth::user()->employeeid,
                        ]);

                        // Delete the patient charge log
                        $patientChargeLogs->delete();

                        // If the entire quantity wasn't returned, create a new log for the remaining quantity
                        if ((int)$previousPatientChargeLogs->quantity != (int)$upd_QtyToReturn) {
                            PatientChargeLogs::create([
                                'enccode' => $previousPatientChargeLogs->enccode,
                                'acctno' => $previousPatientChargeLogs->acctno,
                                'ward_stocks_id' => $previousPatientChargeLogs->ward_stocks_id,
                                'itemcode' => $previousPatientChargeLogs->itemcode,
                                'from' => $previousPatientChargeLogs->from,
                                'manufactured_date' => Carbon::parse($previousPatientChargeLogs->manufactured_date)->format('Y-m-d H:i:s.v'),
                                'delivery_date' => Carbon::parse($previousPatientChargeLogs->delivered_date)->format('Y-m-d H:i:s.v'),
                                'expiration_date' => Carbon::parse($previousPatientChargeLogs->expiration_date)->format('Y-m-d H:i:s.v'),
                                'quantity' => (int)$previousPatientChargeLogs->quantity - (int)$upd_QtyToReturn,
                                'price_per_piece' => $previousPatientChargeLogs->price_per_piece,
                                'price_total' => ((float)$previousPatientChargeLogs->quantity - (float)$upd_QtyToReturn) * (float)$previousPatientChargeLogs->price_per_piece,
                                'pcchrgdte' => $previousPatientChargeLogs->pcchrgdte,
                                'tscode' => $request->tscode,
                                'entry_at' => $authWard,
                                'entry_by' => $authID,
                                'created_at' => $previousPatientChargeLogs->created_at,
                                'updated_at' => $previousPatientChargeLogs->updated_at,
                                'pcchrgcod' => $previousPatientChargeLogs->pcchrgcod, // charge slip no.
                            ]);
                        }
                        // s
                        // Update or delete the patient charge based on the remaining quantity
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
                    } else {
                        // misc
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
                                'itemcode' => $previousPatientChargeLogs->itemcode,
                                'from' => $previousPatientChargeLogs->from,
                                'manufactured_date' => Carbon::parse($previousPatientChargeLogs->manufactured_date)->format('Y-m-d H:i:s.v'),
                                'delivery_date' => Carbon::parse($previousPatientChargeLogs->delivered_date)->format('Y-m-d H:i:s.v'),
                                'expiration_date' => Carbon::parse($previousPatientChargeLogs->expiration_date)->format('Y-m-d H:i:s.v'),
                                'quantity' => (int)$previousPatientChargeLogs->quantity - (int)$upd_QtyToReturn,
                                'price_per_piece' => $previousPatientChargeLogs->price_per_piece,
                                'price_total' => ((float)$previousPatientChargeLogs->quantity - (float)$upd_QtyToReturn) * (float)$previousPatientChargeLogs->price_per_piece,
                                'pcchrgdte' => $previousPatientChargeLogs->pcchrgdte,
                                'tscode' => $request->tscode,
                                'entry_at' => $authWard,
                                'entry_by' => $authID,
                                'created_at' => $previousPatientChargeLogs->created_at,
                                'updated_at' => $previousPatientChargeLogs->updated_at,
                                'pcchrgcod' => $previousPatientChargeLogs->pcchrgcod, // charge slip no.
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
