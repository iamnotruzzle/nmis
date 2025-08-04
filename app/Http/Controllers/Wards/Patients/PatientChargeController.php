<?php

namespace App\Http\Controllers\Wards\Patients;


use App\Events\RequestStock;
use App\Http\Controllers\Controller;
use App\Jobs\ChargingWardConsumptionBatchJob;
use App\Jobs\ChargingWardConsumptionTrackerJobs;
use App\Jobs\CreatePatientChargeLogsJobs;
use App\Models\AdmissionLog;
use App\Models\CsrwCode;
use App\Models\ERlog;
use App\Models\Item;
use App\Models\LocationStockBalance;
use App\Models\LocationStockBalanceDateLogs;
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
use App\Models\WardConsumptionTracker;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use PhpParser\Node\Stmt\TryCatch;
use stdClass;

class PatientChargeController extends Controller
{
    private function getAuthWardcode()
    {
        return DB::select(
            "SELECT TOP 1
                l.wardcode
            FROM
                user_acc u
            INNER JOIN
                csrw_login_history l ON u.employeeid = l.employeeid
            WHERE
                l.employeeid = ?
            ORDER BY
                l.created_at DESC;
            ",
            [Auth::user()->employeeid]
        );
    }

    public function index(Request $request)
    {
        $pat_enccode = $request->enccode;
        $hpercode = $request->hpercode;
        $patient_name = $request->patient;
        $is_for_discharge = $request->disch;
        $room_bed = $request->room_bed;
        $pat_tscode = $request->tscode;
        // dd($pat_tscode);

        // get auth wardcode
        $authWardcode = $this->getAuthWardcode();
        $authCode = $authWardcode[0]->wardcode;

        $medicalSupplies = DB::select(
            "SELECT
                stock.[from],
                stock.id,
                stock.request_stocks_id,
                stock.is_consumable,
                item.cl2comb,
                item.cl2desc,
                item.uomcode,
                stock.quantity,
                stock.average,
                stock.total_usage,
                CASE
                    WHEN stock.[from] = 'CSR' THEN price_csr.price_per_unit
                    ELSE price_other.price_per_unit
                END as price,
                stock.expiration_date,
                stock.created_at
            FROM csrw_wards_stocks stock
            INNER JOIN hclass2 item ON stock.cl2comb = item.cl2comb
            LEFT JOIN csrw_request_stocks rs ON rs.id = stock.request_stocks_id
            LEFT JOIN csrw_item_prices price_csr
                ON stock.cl2comb = price_csr.cl2comb
                AND price_csr.item_conversion_id = stock.stock_id
                AND stock.[from] = 'CSR'
            LEFT JOIN csrw_item_prices price_other
                ON stock.cl2comb = price_other.cl2comb
                AND price_other.ris_no = stock.ris_no
                AND stock.[from] <> 'CSR'
            WHERE stock.location = ?
                AND stock.quantity > 0
                AND (
                    stock.[from] = 'MEDICAL GASES'
                    OR stock.[from] NOT IN ('CSR', 'WARD', 'MEDICAL GASES')
                    OR (
                        stock.[from] IN ('CSR', 'WARD')
                        AND (rs.id IS NULL OR rs.status = 'RECEIVED')
                        AND stock.expiration_date > CAST(GETDATE() AS DATE)
                    )
                )
            ORDER BY stock.[from], item.cl2desc",
            [$authCode]
        );
        $packages = DB::select(
            "SELECT package.id, package.description, pack_dets.cl2comb, item.cl2desc, pack_dets.quantity, package.status
                    FROM csrw_packages AS package
                    JOIN csrw_package_details as pack_dets ON pack_dets.package_id = package.id
                    JOIN hclass2 as item ON item.cl2comb = pack_dets.cl2comb
                    WHERE package.status = 'A'
                    -- AND package.wardcode = ?
                    ORDER BY item.cl2desc ASC;",
        );
        $genericVariants = DB::select(
            "SELECT
                gv.generic_cl2comb,
                gv.variant_cl2comb,
                generic_item.cl2desc AS generic_desc,
                variant_item.cl2desc AS variant_desc
            FROM
                csrw_generic_variants AS gv
            JOIN
                hclass2 AS generic_item ON generic_item.cl2comb = gv.generic_cl2comb
            JOIN
                hclass2 AS variant_item ON variant_item.cl2comb = gv.variant_cl2comb;"
        );
        $misc = Miscellaneous::with('unit')
            ->where('hmstat', 'A')
            ->get(['hmcode', 'hmdesc', 'hmamt', 'uomcode']);

        // // old
        // $bills = DB::select(
        //     "SELECT pat_charge.pcchrgcod as charge_slip_no,
        //         type_of_charge.chrgcode as type_of_charge_code,
        //         type_of_charge.chrgdesc as type_of_charge_description,
        //         category.cl1desc as category,
        //         item.cl2desc as item,
        //         misc.hmdesc as misc,
        //         pat_charge.itemcode as itemcode,
        //         pat_charge.pchrgqty as quantity,
        //         pat_charge.pchrgup as price,
        //         pat_charge.uomcode as uomcode,
        //         pat_charge.pcchrgdte as charge_date,
        //         charge_log.id as charge_log_id,
        //         charge_log.[from] as charge_log_from,
        //         charge_log.ward_stocks_id as charge_log_ward_stocks_id,
        //         charge_log.quantity as charge_log_quantity,
        //         charge_log.expiration_date as charge_log_expiration_date,
        //         charge_by.firstname + ' ' + charge_by.lastname as entry_by,
        //         charge_log.entry_at as entry_at
        //         FROM hospital.dbo.hpatchrg pat_charge
        //         LEFT JOIN hospital.dbo.hclass2 as item ON pat_charge.itemcode = item.cl2comb
        //         LEFT JOIN hospital.dbo.hclass1 as category ON item.cl1comb = category.cl1comb
        //         LEFT JOIN hospital.dbo.hmisc as misc ON pat_charge.itemcode = misc.hmcode
        //         LEFT JOIN hospital.dbo.hpersonal as charge_by ON pat_charge.entryby = charge_by.employeeid
        //         LEFT JOIN hospital.dbo.hcharge as type_of_charge ON pat_charge.chargcode = type_of_charge.chrgcode
        //         LEFT JOIN hospital.dbo.csrw_patient_charge_logs as charge_log ON pat_charge.enccode = charge_log.enccode
        //                                                                     AND pat_charge.pcchrgdte = charge_log.pcchrgdte
        //                                                                     AND pat_charge.itemcode = charge_log.itemcode
        //         WHERE pat_charge.enccode = ?
        //         AND  (type_of_charge.chrgcode = 'DRUMD' OR type_of_charge.chrgcode = 'DRUMN' OR type_of_charge.chrgcode = 'MISC')
        //         ORDER BY pat_charge.pcchrgdte DESC",
        //     [$pat_enccode]
        // );

        // // new
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
                charge_by.firstname + ' ' + charge_by.lastname as entry_by,
                charge_log.entry_at as entry_at
                FROM hospital.dbo.hpatchrg pat_charge
                -- LEFT JOIN hospital.dbo.hclass2 as item ON pat_charge.itemcode = item.cl2comb
                OUTER APPLY (
                    SELECT TOP 1 cl2desc, cl1comb
                    FROM hospital.dbo.hclass2
                    WHERE hclass2.cl2comb = pat_charge.itemcode
                ) AS item
                OUTER APPLY (
                    SELECT TOP 1 cl1desc
                    FROM hospital.dbo.hclass1
                    WHERE hclass1.cl1comb = item.cl1comb
                ) AS category
                OUTER APPLY (
                    SELECT TOP 1 hmdesc
                    FROM hospital.dbo.hmisc
                    WHERE hmisc.hmcode = pat_charge.itemcode
                ) AS misc
                OUTER APPLY (
                    SELECT TOP 1 firstname, lastname
                    FROM hospital.dbo.hpersonal
                    WHERE employeeid = pat_charge.entryby
                ) AS charge_by
                OUTER APPLY (
                    SELECT TOP 1 chrgcode, chrgdesc
                    FROM hospital.dbo.hcharge
                    WHERE chrgcode = pat_charge.chargcode
                ) AS type_of_charge
                LEFT JOIN hospital.dbo.csrw_patient_charge_logs as charge_log ON pat_charge.enccode = charge_log.enccode
                                                                            AND pat_charge.pcchrgdte = charge_log.pcchrgdte
                                                                            AND pat_charge.itemcode = charge_log.itemcode
                -- OUTER APPLY (
                --     SELECT TOP 1 id, [from], ward_stocks_id, quantity, expiration_date, entry_at
                --     FROM hospital.dbo.csrw_patient_charge_logs
                --     WHERE enccode = pat_charge.enccode
                --     AND pcchrgdte = pat_charge.pcchrgdte
                --     AND itemcode = pat_charge.itemcode
                --     ORDER BY id DESC
                -- ) AS charge_log
                WHERE pat_charge.enccode = ?
                AND  (type_of_charge.chrgcode = 'DRUMD' OR type_of_charge.chrgcode = 'DRUMN' OR type_of_charge.chrgcode = 'MISC')
                ORDER BY pat_charge.pcchrgdte DESC",
            [$pat_enccode]
        );

        $canTransact = TransactionService::canTransact($authCode);

        return Inertia::render('Wards/Patients/Bill/Index', [

            'medicalSupplies' => $medicalSupplies,
            'packages' => $packages,
            'genericVariants' => $genericVariants,
            'misc' => $misc,

            'hpercode' => $hpercode,
            'patient_name' => $patient_name,
            'pat_tscode' => $pat_tscode,
            'pat_enccode' => $pat_enccode,
            'room_bed' => $room_bed,
            'bills' => $bills,
            'is_for_discharge' => $is_for_discharge,
            'canTransact' => $canTransact,
        ]);
    }

    public function getWardMedSupplies()
    {
        $authWardcode = DB::select(
            "SELECT TOP 1
                l.wardcode
                FROM
                    user_acc u
                INNER JOIN
                    csrw_login_history l ON u.employeeid = l.employeeid
                WHERE
                    l.employeeid = ?
                ORDER BY
                    l.created_at DESC;
                ",
            [Auth::user()->employeeid]
        );
        $authCode = $authWardcode[0]->wardcode;

        $medicalSupplies = DB::select(
            "SELECT
                stock.[from],
                stock.id,
                stock.request_stocks_id,
                stock.is_consumable,
                item.cl2comb,
                item.cl2desc,
                item.uomcode,
                stock.quantity,
                stock.average,
                stock.total_usage,
                CASE
                    WHEN stock.[from] = 'CSR' THEN price_csr.price_per_unit
                    ELSE price_other.price_per_unit
                END as price,
                stock.expiration_date,
                stock.created_at
            FROM csrw_wards_stocks stock
            INNER JOIN hclass2 item ON stock.cl2comb = item.cl2comb
            LEFT JOIN csrw_request_stocks rs ON rs.id = stock.request_stocks_id
            LEFT JOIN csrw_item_prices price_csr
                ON stock.cl2comb = price_csr.cl2comb
                AND price_csr.item_conversion_id = stock.stock_id
                AND stock.[from] = 'CSR'
            LEFT JOIN csrw_item_prices price_other
                ON stock.cl2comb = price_other.cl2comb
                AND price_other.ris_no = stock.ris_no
                AND stock.[from] <> 'CSR'
            WHERE stock.location = ?
                AND stock.quantity > 0
                AND (
                    stock.[from] = 'MEDICAL GASES'
                    OR stock.[from] NOT IN ('CSR', 'WARD', 'MEDICAL GASES')
                    OR (
                        stock.[from] IN ('CSR', 'WARD')
                        AND (rs.id IS NULL OR rs.status = 'RECEIVED')
                        AND stock.expiration_date > CAST(GETDATE() AS DATE)
                    )
                )
            ORDER BY stock.[from], item.cl2desc",
            [$authCode]
        );

        return response()->json($medicalSupplies);
    }
    public function getPackages()
    {
        $packages = DB::select(
            "SELECT package.id, package.description, pack_dets.cl2comb, item.cl2desc, pack_dets.quantity, package.status
                    FROM csrw_packages AS package
                    JOIN csrw_package_details as pack_dets ON pack_dets.package_id = package.id
                    JOIN hclass2 as item ON item.cl2comb = pack_dets.cl2comb
                    WHERE package.status = 'A'
                    -- AND package.wardcode = ?
                    ORDER BY item.cl2desc ASC;",
        );

        return response()->json($packages);
    }
    public function getGenericVariant()
    {
        $genericVariants = DB::select(
            "SELECT
                gv.generic_cl2comb,
                gv.variant_cl2comb,
                generic_item.cl2desc AS generic_desc,
                variant_item.cl2desc AS variant_desc
            FROM
                csrw_generic_variants AS gv
            JOIN
                hclass2 AS generic_item ON generic_item.cl2comb = gv.generic_cl2comb
            JOIN
                hclass2 AS variant_item ON variant_item.cl2comb = gv.variant_cl2comb;"
        );

        return response()->json($genericVariants);
    }
    public function getMisc()
    {
        $misc = Miscellaneous::with('unit')
            ->where('hmstat', 'A')
            ->get(['hmcode', 'hmdesc', 'hmamt', 'uomcode']);

        return response()->json($misc);
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
        // dd($request);
        $data = $request;

        $entryby = Auth::user()->employeeid;

        $srcchrg = 'WARD';

        $authWardcode = DB::select(
            "SELECT TOP 1
                l.wardcode
                FROM
                    user_acc u
                INNER JOIN
                    csrw_login_history l ON u.employeeid = l.employeeid
                WHERE
                    l.employeeid = ?
                ORDER BY
                    l.created_at DESC;
                ",
            [Auth::user()->employeeid]
        );
        $authCode = $authWardcode[0]->wardcode;

        $enccode = $request->enccode;
        $hospitalNumber = $request->hospitalNumber;
        $itemsToBillList = $request->itemsToBillList;

        // sort the items by itemDesc
        usort($itemsToBillList, function ($a, $b) {
            return strcmp($a["itemDesc"], $b["itemDesc"]); // Ascending order
        });

        $pcchrgcod = $this->generateUniqueChargeCode();
        $processedItems = []; // Store processed item codes with their pcchrgcod

        // init tscode
        $tscode = $request->tscode;

        // Initialize batch data collection
        $consumptionJobData = [];

        // STEP 1: check if the request is a new charge or modifying a charge
        if ($request->isUpdate == false) {
            // STEP 2: check patient account
            $r = PatientAccount::where('enccode', $enccode)->first(['paacctno']);
            $acctno = $r != null ? $r->paacctno : '';

            // Prepare arrays for bulk operations
            $patientCharges = [];
            $wardStockUpdates = [];
            $chargeLogs = [];

            DB::transaction(function () use (
                $itemsToBillList,
                $enccode,
                $hospitalNumber,
                $pcchrgcod,
                $srcchrg,
                $acctno,
                $entryby,
                $authCode,
                $tscode,
                &$patientCharges,
                &$wardStockUpdates,
                &$chargeLogs
            ) {
                $processedItems = [];

                // dd($itemsToBillList);

                foreach ($itemsToBillList as $item) {
                    // STEP 3.1: Handle charge code generation
                    if (!isset($processedItems[$item['itemCode']])) {
                        $processedItems[$item['itemCode']] = $pcchrgcod;
                    } else {
                        $processedItems[$item['itemCode']] = $this->generateUniqueChargeCode();
                    }

                    // STEP 4: Prepare patient charge data
                    $now = Carbon::now();
                    $patientCharges[] = [
                        'enccode' => $enccode,
                        'hpercode' => $hospitalNumber,
                        'pcchrgcod' => $processedItems[$item['itemCode']],
                        'pcchrgdte' => $now,
                        'chargcode' => $item['typeOfCharge'],
                        'uomcode' => $item['unit'],
                        'pchrgqty' => (float)$item['qtyToCharge'],
                        'pchrgup' => (float)$item['price'],
                        'pcchrgamt' => (float)$item['total'],
                        'pcstat' => 'A',
                        'pclock' => 'N',
                        'updsw' => 'N',
                        'confdl' => 'N',
                        'srcchrg' => $srcchrg,
                        'pcdisch' => 'Y',
                        'acctno' => $acctno,
                        'itemcode' => $item['itemCode'],
                        'entryby' => $entryby,
                        // 'created_at' => $now,
                        // 'updated_at' => $now,
                    ];

                    $quantity_to_insert_in_logs = $item['qtyToCharge'];

                    // STEP 5: Handle medical supplies or misc
                    if ($item['typeOfCharge'] == 'DRUMN') {
                        $wardStock = WardsStocks::where('cl2comb', $item['itemCode'])
                            ->where('location', $authCode)
                            ->where('id', $item['id'])
                            ->lockForUpdate() // Prevent concurrent modifications
                            ->first();

                        if ($wardStock->is_consumable != 'y') {
                            // Regular medical supplies
                            $newStockQty = $wardStock->quantity - $item['qtyToCharge'];
                            $wardStockUpdates[] = [
                                'id' => $wardStock->id,
                                'quantity' => $newStockQty
                            ];
                        } else {
                            // Medical gas
                            $newTotalUsage = $wardStock->total_usage - $item['qtyToCharge'];
                            $fullTanks = (int) floor($newTotalUsage / $wardStock->average);
                            $remainingInLastTank = $newTotalUsage % $wardStock->average;
                            $newQuantity = $fullTanks + ($remainingInLastTank > 0 ? 1 : 0);

                            $wardStockUpdates[] = [
                                'id' => $wardStock->id,
                                'total_usage' => $newTotalUsage,
                                'quantity' => $newQuantity
                            ];
                        }

                        $chargeLogs[] = [
                            'enccode' => $enccode,
                            'acctno' => $acctno,
                            'ward_stocks_id' => $wardStock->id,
                            'itemcode' => $wardStock->cl2comb,
                            'from' => $wardStock->from,
                            'manufactured_date' => $wardStock->manufactured_date,
                            'delivery_date' => $wardStock->delivery_date,
                            'expiration_date' => $wardStock->expiration_date,
                            'quantity' => (float)$quantity_to_insert_in_logs,
                            'price_per_piece' => (float)$item['price'] ?? null,
                            'price_total' => (float)$quantity_to_insert_in_logs * (float)$item['price'],
                            'pcchrgdte' => $now,
                            'entry_at' => $authCode,
                            'entry_by' => $entryby,
                            'pcchrgcod' => $processedItems[$item['itemCode']],
                            'tscode' => $tscode,
                        ];

                        #region comment for now
                        $ward_stock_id = $wardStock->id;
                        $non_specific_charge = $quantity_to_insert_in_logs;

                        $consumptionJobData[] = [
                            'ward_stock_id' => $ward_stock_id,
                            'non_specific_charge' => $non_specific_charge,
                            'tscode' => $tscode
                        ];

                        #endregion comment for now
                    }

                    // MISC items
                    if ($item['typeOfCharge'] == 'MISC') {
                        $chargeLogs[] = [
                            'enccode' => $enccode,
                            'acctno' => $acctno,
                            'ward_stocks_id' => null,
                            'itemcode' => $item['itemCode'],
                            'from' => null,
                            'manufactured_date' => null,
                            'delivery_date' => null,
                            'expiration_date' => null,
                            'quantity' => (float)$quantity_to_insert_in_logs,
                            'price_per_piece' => (float)$item['price'] ?? null,
                            'price_total' => (float)$quantity_to_insert_in_logs * (float)$item['price'],
                            'pcchrgdte' => $now,
                            'entry_at' => $authCode,
                            'entry_by' => $entryby,
                            'pcchrgcod' => $processedItems[$item['itemCode']],
                            'tscode' => $tscode
                        ];
                    }
                }

                // After the foreach loop, dispatch one batch job instead of many individual jobs:
                if (!empty($consumptionJobData)) {
                    ChargingWardConsumptionBatchJob::dispatch($consumptionJobData);
                }

                // Bulk insert patient charges
                if (!empty($patientCharges)) {
                    PatientCharge::insert($patientCharges);
                }
                // dd($chargeLogs);

                // Bulk update ward stocks
                foreach ($wardStockUpdates as $update) {
                    $id = $update['id'];
                    $updateData = array_diff_key($update, ['id' => '']); // Exclude 'id' key

                    WardsStocks::where('id', $id)->update($updateData);
                }

                if (!empty($chargeLogs)) {
                    PatientChargeLogs::insert($chargeLogs);
                }
            });

            // OLD method using jobs
            // Dispatch logs after successful transaction
            // if (!empty($chargeLogs)) {
            //     CreatePatientChargeLogsJobs::dispatch($chargeLogs);
            // }
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

                        $upd_QtyToReturn = (int)$upd_QtyToReturn;
                        $upd_ward_stocks_id = $request->upd_ward_stocks_id;

                        $this->voidingConsumptionForTrackerLog(
                            $upd_ward_stocks_id,
                            $upd_QtyToReturn,
                        );

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

    public function voidingConsumptionForTrackerLog(
        $upd_ward_stocks_id,
        $upd_QtyToReturn,
    ) {
        $latestRecord = WardConsumptionTracker::where('ward_stock_id', $upd_ward_stocks_id)
            ->latest()
            ->first();

        // only update the ward consumption tracker if the $upd_ward_stocks_id received
        // is NOT FROM = consignment, delivery, supplemental
        // update only if its existing_stock or from csr
        if ($latestRecord) {
            $latestRecord->update([
                'non_specific_charge' => DB::raw("non_specific_charge - {$upd_QtyToReturn}")
            ]);
        }
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
