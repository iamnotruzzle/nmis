<?php

namespace App\Jobs;

use App\Models\WardConsumptionTracker;
use App\Models\WardsStocks;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ChargingWardConsumptionTrackerJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // jobs
    public $tries = 5;

    public $ward_stocks_id;
    public $quantity;

    public function __construct($ward_stocks_id, $quantity)
    {
        $this->ward_stocks_id = $ward_stocks_id;
        $this->quantity = $quantity;
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
        //         'charged_qty' => DB::raw("charged_qty + {$this->quantity}")
        //     ]);

        WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->update([
                'charged_qty' => DB::raw("charged_qty + {$this->quantity}")
            ]);
    }

    public function failed(\Throwable $e)
    {
        // WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
        //     ->update([
        //         'charged_qty' => DB::raw("charged_qty + {$this->quantity}")
        //     ]);

        WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
            ->orderBy('created_at', 'DESC')
            ->limit(1)
            ->update([
                'charged_qty' => DB::raw("charged_qty + {$this->quantity}")
            ]);
    }
}
