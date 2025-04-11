<?php

namespace App\Jobs;

use App\Models\WardConsumptionTracker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

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
        $existingTracker = WardConsumptionTracker::where('cl2comb', $this->cl2comb)
            ->where('price_id', $this->price_id)
            ->where('ward_stock_id', $this->id)
            ->whereNull('end_bal_date')
            ->first();

        // Check if the record already exists for the period
        $existingTracker = WardConsumptionTracker::where('ward_stock_id', $this->id)
            ->where('cl2comb', $this->cl2comb)
            ->where('price_id', $this->price_id)
            ->whereNull('end_bal_date')
            ->exists();

        if (!$existingTracker) {
            WardConsumptionTracker::create([
                'ward_stock_id'    => $this->id,
                'item_conversion_id' => $this->item_conversion_id,
                'ris_no'           => $this->ris_no,
                'cl2comb'          => $this->cl2comb,
                'uomcode'          => $this->uomcode,
                'initial_qty'      => $this->initial_qty,
                'beg_bal_date'     => $this->beg_bal_date,
                'beg_bal_qty'      => $this->quantity,
                'location'         => $this->location,
                'item_from'        => $this->from,
                'price_id'         => $this->price_id,
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
