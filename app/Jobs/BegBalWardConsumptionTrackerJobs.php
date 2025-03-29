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
    public $location;
    public $price_id;
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
        $this->beg_bal_date = $beg_bal_date;
    }

    public function handle()
    {
        $latestRecord = WardConsumptionTracker::where('wards_stocks_id', $this->id)
            // ->whereNull('beg_bal_date')
            // ->whereNull('end_bal_date')
            ->latest()
            ->first();

        if ($latestRecord->beg_bal_date == NULL && $latestRecord->end_bal_date == NULL) {
            // If a record exists, update it
            $latestRecord->update([
                'beg_bal_qty' => $this->quantity,
                'beg_bal_date' => $this->beg_bal_date,
            ]);
        } else {
            WardConsumptionTracker::create([
                'wards_stocks_id' => $this->id,
                'item_conversion_id' => $this->item_conversion_id,
                'ris_no' => $this->ris_no,
                'cl2comb' => $this->cl2comb,
                'uomcode' => $this->uomcode,
                'beg_bal_qty' => $this->quantity,
                'beg_bal_date' => $this->beg_bal_date,
                'end_bal_date' => null,
                'item_from' => 'CSR',
                'location' => $this->location,
                'price_id' => $this->price_id,
            ]);
        }

        // if ($latestRecord) {
        //     // If a record exists, update it
        //     $latestRecord->update([
        //         'beg_bal_qty' => $this->quantity,
        //         'beg_bal_date' => $this->beg_bal_date,
        //     ]);
        // }
    }

    public function failed(\Throwable $e)
    {
        $latestRecord = WardConsumptionTracker::where('wards_stocks_id', $this->id)
            // ->whereNull('beg_bal_date')
            // ->whereNull('end_bal_date')
            ->latest()
            ->first();

        if ($latestRecord->beg_bal_date == NULL && $latestRecord->end_bal_date == NULL) {
            // If a record exists, update it
            $latestRecord->update([
                'beg_bal_qty' => $this->quantity,
                'beg_bal_date' => $this->beg_bal_date,
            ]);
        } else {
            WardConsumptionTracker::create([
                'wards_stocks_id' => $this->id,
                'item_conversion_id' => $this->item_conversion_id,
                'ris_no' => $this->ris_no,
                'cl2comb' => $this->cl2comb,
                'uomcode' => $this->uomcode,
                'beg_bal_qty' => $this->quantity,
                'beg_bal_date' => $this->beg_bal_date,
                'end_bal_date' => null,
                'item_from' => 'CSR',
                'location' => $this->location,
                'price_id' => $this->price_id,
            ]);
        }
    }
}
