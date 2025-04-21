<?php

namespace App\Jobs;

use App\Models\WardConsumptionTracker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReceiveItemAfterBegBalJobs implements ShouldQueue
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
    public $location;
    public $price_id;
    public $from;

    public function __construct(
        $id,
        $item_conversion_id,
        $ris_no,
        $cl2comb,
        $uomcode,
        $quantity,
        $location,
        $price_id,
        $from
    ) {
        $this->id = $id;
        $this->item_conversion_id = $item_conversion_id;
        $this->ris_no = $ris_no;
        $this->cl2comb = $cl2comb;
        $this->uomcode = $uomcode;
        $this->quantity = $quantity;
        $this->location = $location;
        $this->price_id = $price_id;
        $this->from = $from;
    }


    public function handle()
    {
        // Check if this stock already exists in the tracker with no end balance (meaning it's still in progress)
        $existingTracker = WardConsumptionTracker::where('ward_stock_id', $this->id)
            ->where('cl2comb', $this->cl2comb)
            ->where('price_id', $this->price_id)
            ->whereNull('end_bal_date')
            ->exists();

        if (!$existingTracker) {
            // New stock has been received after beginning balance, so create a new row
            WardConsumptionTracker::create([
                'ward_stock_id'    => $this->id,
                'item_conversion_id' => $this->item_conversion_id,
                'ris_no'           => $this->ris_no,
                'cl2comb'          => $this->cl2comb,
                'uomcode'          => $this->uomcode,
                'initial_qty'      => $this->quantity,
                'beg_bal_date'     => null, // intentionally left null
                'beg_bal_qty'      => 0, // intentionally left null
                'location'         => $this->location,
                'item_from'        => $this->from, // Whether it's from CSR or a ward
                'price_id'         => $this->price_id,
            ]);
        }
    }

    public function failed(\Throwable $e) {}
}
