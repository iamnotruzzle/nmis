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
        // Step 1: Get the current active balance period for this ward/location
        $currentPeriod = DB::table('csrw_stock_bal_date_logs')
            ->where('wardcode', $this->location)
            ->whereNotNull('beg_bal_created_at')
            ->whereNull('end_bal_created_at') // only periods that haven't been closed yet
            ->latest('beg_bal_created_at')
            ->first();

        if (!$currentPeriod) {
            \Log::warning('No open beginning balance period found for location', [
                'location' => $this->location
            ]);
            return;
        }

        // Step 2: Find the correct tracker row
        $tracker = WardConsumptionTracker::where('ward_stock_id', $this->id)
            ->where('cl2comb', $this->cl2comb)
            ->where('price_id', $this->price_id)
            ->where('location', $this->location)
            ->whereNull('end_bal_date') // still open
            ->whereNull('beg_bal_date') // no beginning balance yet
            ->whereDate('created_at', '>=', Carbon::parse($currentPeriod->beg_bal_created_at)) // optional filter by period
            ->latest('created_at')
            ->first();

        if ($tracker) {
            $tracker->update([
                'beg_bal_date' => $this->beg_bal_date, // or $currentPeriod->beg_bal_created_at
                'beg_bal_qty'  => $this->quantity,
            ]);
        } else {
            \Log::warning('No tracker row found for beginning balance', [
                'ward_stock_id' => $this->id,
                'cl2comb' => $this->cl2comb,
                'price_id' => $this->price_id,
                'location' => $this->location,
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
