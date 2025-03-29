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
    public $quantity;
    public $beg_bal_date;

    public function __construct(
        $id,
        $quantity,
        $beg_bal_date,
    ) {
        $this->id = $id;
        $this->quantity = $quantity;
        $this->beg_bal_date = $beg_bal_date;
    }

    public function handle()
    {
        $latestRecord = WardConsumptionTracker::where('wards_stocks_id', $this->id)
            ->whereNull('beg_bal_date')
            ->whereNull('end_bal_date')
            ->latest()
            ->first();

        if ($latestRecord) {
            // If a record exists, update it
            $latestRecord->update([
                'beg_bal_qty' => $this->quantity,
                'beg_bal_date' => $this->beg_bal_date,
            ]);
        }
    }

    public function failed(\Throwable $e)
    {
        $latestRecord = WardConsumptionTracker::where('wards_stocks_id', $this->id)
            ->whereNull('beg_bal_date')
            ->whereNull('end_bal_date')
            ->latest()
            ->first();

        if ($latestRecord) {
            // If a record exists, update it
            $latestRecord->update([
                'beg_bal_qty' => $this->quantity,
                'beg_bal_date' => $this->beg_bal_date,
            ]);
        }
    }
}
