<?php

namespace App\Jobs;

use App\Models\WardConsumptionTracker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReceiveItemFromWardJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // jobs
    public $tries = 5;

    public $id;
    public $item_conversion_id;
    public $cl2comb;
    public $ris_no;
    public $uomcode;
    public $initial_qty;
    public $from;
    public $location;
    public $price_id;

    public function __construct(
        $id,
        $item_conversion_id,
        $cl2comb,
        $ris_no,
        $uomcode,
        $initial_qty,
        $from,
        $location,
        $price_id,
    ) {
        $this->id = $id;
        $this->item_conversion_id = $item_conversion_id;
        $this->cl2comb = $cl2comb;
        $this->ris_no = $ris_no;
        $this->uomcode = $uomcode;
        $this->initial_qty = $initial_qty;
        $this->from = $from;
        $this->location = $location;
        $this->price_id = $price_id;
    }


    public function handle()
    {
        WardConsumptionTracker::create([
            'ward_stock_id'    => $this->id,
            'item_conversion_id' => $this->item_conversion_id,
            'ris_no'           => $this->ris_no,
            'cl2comb'          => $this->cl2comb,
            'uomcode'          => $this->uomcode,
            'initial_qty'      => $this->initial_qty,
            'beg_bal_date'     => null, // intentionally left null
            'beg_bal_qty'      => 0, // intentionally left null
            'location'         => $this->location,
            'item_from'        => $this->from, // Whether it's from CSR or a ward
            'price_id'         => $this->price_id,
        ]);
    }

    public function failed(\Throwable $e) {}
}
