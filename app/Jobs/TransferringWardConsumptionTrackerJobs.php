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

class TransferringWardConsumptionTrackerJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // jobs
    public $tries = 5;

    public $ward_stocks_id;
    public $transferred_qty;

    public function __construct($ward_stocks_id, $transferred_qty)
    {
        $this->ward_stocks_id = $ward_stocks_id;
        $this->transferred_qty = $transferred_qty;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
        //     ->update([
        //         'transfer_qty' => DB::raw("transfer_qty + {$this->transferred_qty}")
        //     ]);

        WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
            ->latest() // Orders by created_at DESC to get the most recent row
            ->first()
            ->update([
                'transfer_qty' => DB::raw("transfer_qty + {$this->transferred_qty}")
            ]);
    }

    public function failed(\Throwable $e)
    {
        // WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
        //     ->update([
        //         'transfer_qty' => DB::raw("transfer_qty + {$this->transferred_qty}")
        //     ]);

        WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
            ->latest() // Orders by created_at DESC to get the most recent row
            ->first()
            ->update([
                'transfer_qty' => DB::raw("transfer_qty + {$this->transferred_qty}")
            ]);
    }
}
