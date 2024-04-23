<?php

namespace App\Http\Controllers\Wards\UpdateRequestedMedsFS;

use App\Http\Controllers\Controller;
use App\Models\MedsRequest;
use App\Models\WardsStocksMeds;
use Illuminate\Http\Request;

class UpdateRequestedMedsFSController extends Controller
{
    public function store(Request $request)
    {
        // dd($request->fsId);


        MedsRequest::where('id', $request->itemId)
            ->update([
                'fsid' => $request->fsId,
            ]);

        // WardsStocksMeds::where('meds_request_id', $request->itemId)
        //     ->update([
        //         'fsid' => $request->fsId,
        //     ]);

        return redirect()->back();
    }

    public function update(MedsRequest $updatereqmedsf, Request $request)
    {
        // dd($request);

        // MedsRequest::where('id', $request->itemId)
        //     ->update([
        //         'fsId' => $request->fsId,
        //     ]);


    }

    public function destroy($id)
    {
        //
    }
}
