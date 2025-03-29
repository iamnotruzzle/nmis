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
    public $quantity;
    public $end_bal_date;

    public function __construct(
        $id,
        $quantity,
        $end_bal_date,
    ) {
        $this->id = $id;
        $this->quantity = $quantity;
        $this->end_bal_date = $end_bal_date;
    }

    public function handle()
    {
        $latestRecord = WardConsumptionTracker::where('wards_stocks_id', $this->id)
            ->latest() // Orders by created_at DESC to get the most recent row
            ->first();

        if ($latestRecord) {
            // Update only the latest record
            $latestRecord->update([
                'end_bal_qty' => $this->quantity,
                'end_bal_date' => $this->end_bal_date,
            ]);
        }
    }

    public function failed(\Throwable $e)
    {
        $latestRecord = WardConsumptionTracker::where('wards_stocks_id', $this->id)
            ->latest() // Orders by created_at DESC to get the most recent row
            ->first();

        if ($latestRecord) {
            // Update only the latest record
            $latestRecord->update([
                'end_bal_qty' => $this->quantity,
                'end_bal_date' => $this->end_bal_date,
            ]);
        }
    }
}
