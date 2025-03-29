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

class ReturnWardConsumptionTrackerJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // jobs
    public $tries = 5;

    public $ward_stocks_id;
    public $returnQty;

    public function __construct($ward_stocks_id, $returnQty)
    {
        $this->ward_stocks_id = $ward_stocks_id;
        $this->returnQty = $returnQty;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
            ->update([
                'return_to_csr_qty' => DB::raw("return_to_csr_qty + {$this->returnQty}")
            ]);
    }

    public function failed(\Throwable $e)
    {
        WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
            ->update([
                'return_to_csr_qty' => DB::raw("return_to_csr_qty + {$this->returnQty}")
            ]);
    }
}
