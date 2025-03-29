<?php

namespace App\Jobs;

use App\Models\WardConsumptionTracker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InitializeWardConsumptionTrackerJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // jobs
    public $tries = 5;

    public $id;
    public $item_conversion_id;
    public $ris_no;
    public $cl2comb;
    public $uomcode;
    public $received_qty;
    public $location;
    public $price_id;

    public function __construct(
        $id,
        $item_conversion_id,
        $ris_no,
        $cl2comb,
        $uomcode,
        $received_qty,
        $location,
        $price_id
    ) {
        $this->id = $id;
        $this->item_conversion_id = $item_conversion_id;
        $this->ris_no = $ris_no;
        $this->cl2comb = $cl2comb;
        $this->uomcode = $uomcode;
        $this->received_qty = $received_qty;
        $this->location = $location;
        $this->price_id = $price_id;
    }


    public function handle()
    {
        $wardConsumptionTracker = WardConsumptionTracker::create([
            'wards_stocks_id' => $this->id,
            'item_conversion_id' => $this->item_conversion_id,
            'ris_no' => $this->ris_no,
            'cl2comb' => $this->cl2comb,
            'uomcode' => $this->uomcode,
            'received_qty' => $this->received_qty,
            'charged_qty' => 0,
            'return_to_csr_qty' => 0,
            'transfer_qty' => 0,
            'item_from' => 'CSR',
            'location' => $this->location,
            'price_id' => $this->price_id,
        ]);
    }
}
