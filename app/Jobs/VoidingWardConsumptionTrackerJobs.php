<?php

namespace App\Jobs;

use App\Models\WardConsumptionTracker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class VoidingWardConsumptionTrackerJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // jobs
    public $tries = 5;

    public $upd_ward_stocks_id;
    public $upd_QtyToReturn;


    public function __construct($upd_ward_stocks_id, $upd_QtyToReturn)
    {
        $this->upd_ward_stocks_id = $upd_ward_stocks_id;
        $this->upd_QtyToReturn = $upd_QtyToReturn;
    }

    public function handle()
    {
        $tracker = WardConsumptionTracker::where('ward_stock_id', $this->upd_ward_stocks_id)
            ->whereNull('end_bal_date')
            ->latest()
            ->first();

        if ($tracker) {
            $tracker->update([
                'charged_qty' => DB::raw("GREATEST(charged_qty - {$this->upd_QtyToReturn}, 0)")
            ]);
        }
    }

    public function failed(\Throwable $e) {}
}
