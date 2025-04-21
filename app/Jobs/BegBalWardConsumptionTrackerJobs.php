<?php

namespace App\Jobs;

use App\Models\WardConsumptionTracker;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BegBalWardConsumptionTrackerJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // jobs
    public $tries = 5;

    public $id;
    public $item_conversion_id;
    public $ris_no;
    public $cl2comb;
    public $uomcode;
    public $quantity;
    public $initial_qty;
    public $location;
    public $price_id;
    public $from;
    public $beg_bal_date;

    public function __construct(
        $id,
        $item_conversion_id,
        $ris_no,
        $cl2comb,
        $uomcode,
        $location,
        $price_id,
        $quantity,
        $initial_qty,
        $from,
        $beg_bal_date,
    ) {
        $this->id = $id;
        $this->item_conversion_id = $item_conversion_id;
        $this->ris_no = $ris_no;
        $this->cl2comb = $cl2comb;
        $this->uomcode = $uomcode;
        $this->location = $location;
        $this->price_id = $price_id;
        $this->quantity = $quantity;
        $this->initial_qty = $initial_qty;
        $this->from = $from;
        $this->beg_bal_date = $beg_bal_date;
    }

    public function handle()
    {
        $date = Carbon::now();
        $begDateTime = $date->copy()->startOfDay(); // Sets time to 00:00:00

        // Step 1: Get the latest balance period for this ward
        $currentPeriod = DB::table('csrw_stock_bal_date_logs')
            ->where('wardcode', $this->location)
            ->latest('beg_bal_created_at')
            ->first();

        // Step 2: If no period exists OR the latest one is already closed, create a new one
        if (!$currentPeriod || $currentPeriod->end_bal_created_at !== null) {
            $newId = DB::table('csrw_stock_bal_date_logs')->insertGetId([
                'wardcode'           => $this->location,
                'beg_bal_created_at' => now(),
                'end_bal_created_at' => null,
            ]);

            $currentPeriod = DB::table('csrw_stock_bal_date_logs')->where('id', $newId)->first();
        }

        // Step 3: Check if tracker row for this stock + price + cl2comb exists (for this open period)
        $tracker = WardConsumptionTracker::where('ward_stock_id', $this->id)
            ->where('cl2comb', $this->cl2comb)
            ->where('price_id', $this->price_id)
            ->where('location', $this->location)
            ->whereNotNull('beg_bal_date')
            ->latest('created_at')
            ->first();

        if ($tracker !== null) {
            // Update open tracker
            $tracker->update([
                'beg_bal_date' => $this->beg_bal_date ?? now(),
                'beg_bal_qty' => $this->quantity,
            ]);
        } else {
            // Create new tracker row
            WardConsumptionTracker::create([
                'ward_stock_id' => $this->id,
                'item_conversion_id' => $this->item_conversion_id,
                'ris_no' => $this->ris_no,
                'cl2comb' => $this->cl2comb,
                'uomcode' => $this->uomcode,
                'beg_bal_date' => $this->beg_bal_date,
                'beg_bal_qty' => $this->quantity,
                'item_from' => $this->from,
                'location' => $this->location,
                'price_id' => $this->price_id,
            ]);
        }
    }



    public function failed(\Throwable $e)
    {
        // \Log::info('Creating WardConsumptionTracker record', [
        //     'ward_stock_id' => $this->id,
        //     'beg_bal_qty' => $this->quantity,
        //     'all_data' => [
        //         'ward_stock_id'    => $this->id,
        //         'item_conversion_id' => $this->item_conversion_id,
        //         'ris_no'           => $this->ris_no,
        //         'cl2comb'          => $this->cl2comb,
        //         'uomcode'          => $this->uomcode,
        //         'initial_qty'      => $this->initial_qty,
        //         'beg_bal_date'     => $this->beg_bal_date,
        //         'beg_bal_qty'      => $this->quantity,
        //         'location'         => $this->location,
        //         'item_from'        => $this->from,
        //         'price_id'         => $this->price_id,
        //     ]
        // ]);
    }
}
