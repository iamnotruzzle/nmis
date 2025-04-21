<?php

namespace App\Jobs;

use App\Models\WardConsumptionTracker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EndBalWardConsumptionTrackerJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    // jobs
    public $tries = 5;

    public $id;
    public $cl2comb;
    public $quantity;
    public $price_id;
    public $end_bal_date;

    public function __construct(
        $id,
        $cl2comb,
        $quantity,
        $price_id,
        $end_bal_date,
    ) {
        $this->id = $id;
        $this->cl2comb = $cl2comb;
        $this->quantity = $quantity;
        $this->price_id = $price_id;
        $this->end_bal_date = $end_bal_date;
    }

    public function handle()
    {
        $tracker = WardConsumptionTracker::where('ward_stock_id', $this->id)
            ->where('cl2comb', $this->cl2comb)
            ->where('price_id', $this->price_id)
            ->whereNull('end_bal_date')
            ->first();

        if ($tracker) {
            // Set ending balance regardless of whether beginning balance exists
            $tracker->update([
                'end_bal_qty'  => $this->quantity,
                'end_bal_date' => $this->end_bal_date,
            ]);
        } else {
            // Optional: log or handle if there's no tracker to update
            \Log::warning("No open tracker found for ending balance", [
                'ward_stock_id' => $this->id,
                'cl2comb'       => $this->cl2comb,
                'price_id'      => $this->price_id,
            ]);
        }
    }


    public function failed(\Throwable $e) {}
}
