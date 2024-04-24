<?php

namespace App\Http\Controllers\Wards\RequestMeds;

use App\Http\Controllers\Controller;
use App\Models\FundSource;
use App\Models\MedsRequest;
use App\Models\WardsStocksMeds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class RequestMedsController extends Controller
{
    public function index()
    {
        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $medicines = [];

        $resultArray = DB::select(
            "SELECT dmdcomb, dmdctr, drug_concat
                FROM hdmhdr
                WHERE dmdstat = 'A'
                AND drug_concat IS NOT NULL
                AND exists (select * from [hospital].[dbo].[hgen] inner join [hospital].[dbo].[hdruggrp] on [hospital].[dbo].[hdruggrp].[gencode] = [hospital].[dbo].[hgen].[gencode] where [hospital].[dbo].[hdmhdr].[grpcode] = [hospital].[dbo].[hdruggrp].[grpcode])
                ORDER BY drug_concat ASC;"
        );
        foreach ($resultArray as $result) {
            $medicines[] = [
                'dmdcomb' => $result->dmdcomb,
                'dmdctr' => $result->dmdctr,
                'drug_concat' => explode('_,', $result->drug_concat)
            ];
        }

        $fundSource = FundSource::get(['id', 'fsid', 'fsName', 'cluster_code']);

        $medsRequest = DB::select(
            "SELECT request.reference_id, request.id, request.dmdprdte, meds.dmdcomb,
                    meds.dmdctr, request.fsid, fs.fsName, meds.drug_concat, request.wardcode,
                    request.selling_price, request.requested_qty, request.approved_qty,
                    request.expiration_date, request.status,
                    request.created_at
                FROM csrw_meds_request as request
                JOIN hdmhdr as meds ON meds.dmdcomb = request.dmdcomb
                LEFT JOIN csrw_fund_source as fs ON fs.fsid = request.fsid
                WHERE request.wardcode = '$authWardcode->wardcode';"
        );

        // dd($medsRequest);

        return Inertia::render('Wards/MedicineStocks/Index', [
            'medicines' => $medicines,
            'fundSource' => $fundSource,
            'medsRequest' => $medsRequest,
        ]);
    }

    // Generate a reference ID
    public static function generateReferenceId()
    {
        // Get the current year
        $currentYear = date('Y');

        // Get the last inserted reference ID for the current year
        $lastReference = MedsRequest::whereYear('created_at', $currentYear)->latest()->first();

        if ($lastReference) {
            // Increment the reference ID
            $lastReferenceId = intval(substr($lastReference->reference_id, 4)); // Extract the numeric part
            $newReferenceId = $lastReferenceId + 1;
        } else {
            // If there's no reference ID for the current year, start from 1
            $newReferenceId = 1;
        }

        // Format the reference ID
        $formattedReferenceId = $currentYear . $newReferenceId;

        return $formattedReferenceId;
    }

    public function store(Request $request)
    {
        // dd($request);

        $authWardcode = DB::table('csrw_users')
            ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
            ->select('csrw_login_history.wardcode')
            ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
            ->orderBy('csrw_login_history.created_at', 'desc')
            ->first();

        $requestStockListDetails = $request->requestStockListDetails;

        // dd($requestStockListDetails);

        $reference_id = $this->generateReferenceId();

        foreach ($requestStockListDetails as $item) {
            MedsRequest::create([
                'reference_id' => $reference_id,
                'dmdcomb' => $item['dmdcomb'],
                'dmdctr' => $item['dmdctr'],
                'requested_qty' => $item['requested_qty'],
                'wardcode' => $authWardcode->wardcode,
                'status' => 'PENDING',
            ]);
        }

        // the parameters result will be send into the frontend
        // event(new RequestStock(RequestStocks::where('status', 'PENDING')->count()));

        return Redirect::route('requestmedsstocks.index');
    }

    public function update(MedsRequest $requestmedsstock, Request $request)
    {
        // dd($request);

        // $authWardcode = DB::table('csrw_users')
        //     ->join('csrw_login_history', 'csrw_users.employeeid', '=', 'csrw_login_history.employeeid')
        //     ->select('csrw_login_history.wardcode')
        //     ->where('csrw_login_history.employeeid', Auth::user()->employeeid)
        //     ->orderBy('csrw_login_history.created_at', 'desc')
        //     ->first();

        MedsRequest::where('id', $request->id)
            ->update([
                'requested_qty' => $request->requested_qty,
                'remarks' => $request->remarks,
            ]);

        return Redirect::route('requestmedsstocks.index');
    }

    public function updatedeliverystatus(MedsRequest $requestmedsstock, Request $request)
    {
        $reference_id = $request->reference_id;

        $requestedStocks = DB::select(
            "SELECT request.reference_id, request.id, request.dmdprdte, meds.dmdcomb,
                meds.dmdctr, meds.drug_concat, request.wardcode,
                request.selling_price, request.requested_qty, request.approved_qty,
                request.expiration_date, request.status, request.remarks,
                request.created_at
            FROM csrw_meds_request as request
            JOIN hdmhdr as meds ON meds.dmdcomb = request.dmdcomb
            WHERE request.reference_id = '$reference_id';"
        );

        // dd($requestedStocks);

        foreach ($requestedStocks as $item) {
            dd($item);
            WardsStocksMeds::create([
                'meds_request_id' => $item->id,
                'reference_id' => $item->reference_id,
                'dmdprdte' => $item->dmdprdte,
                'dmdcomb' => $item->dmdcomb,
                'dmdctr' => $item->dmdctr,
                // 'fsid' => $item->fsid,
                'fsid' => null,
                'selling_price' => $item->selling_price,
                'quantity' => $item->approved_qty,
                'expiration_date' => $item->expiration_date,
                'wardcode' => $item->wardcode,
            ]);
        }

        // update status
        MedsRequest::where('reference_id', $reference_id)
            ->update([
                'status' => 'RECEIVED',
            ]);

        // TODO add function to insert the imte in csrw meds stocks based on the reference_id

        return Redirect::route('requestmedsstocks.index');
    }

    public function destroy(MedsRequest $requestmedsstock, Request $request)
    {
        // dd($request);

        $reference_id = $request->reference_id;

        // delete request stock
        // $requeststock->delete();

        MedsRequest::where('reference_id', $reference_id)
            ->update([
                'status' => 'CANCELLED',
            ]);

        return Redirect::route('requestmedsstocks.index');
    }
}
