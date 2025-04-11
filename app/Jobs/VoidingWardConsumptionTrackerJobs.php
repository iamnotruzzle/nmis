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
        WardConsumptionTracker::where('ward_stock_id', $this->upd_ward_stocks_id)
            ->latest() // Orders by created_at DESC to get the most recent row
            ->first()
            ->update([
                'non_specific_charge' => DB::raw("non_specific_charge - {$this->upd_QtyToReturn}")
            ]);
    }

    public function failed(\Throwable $e) {}
}
