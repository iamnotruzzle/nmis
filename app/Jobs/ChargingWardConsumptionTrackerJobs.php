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

    public $ward_stock_id;
    public $non_specific_charge;
    public $tscode;

    public function __construct($ward_stock_id, $non_specific_charge, $tscode)
    {
        $this->ward_stock_id = $ward_stock_id;
        $this->non_specific_charge = $non_specific_charge;
        $this->tscode = $tscode;
    }

    public function handle()
    {
        if ($this->tscode == 'SURG') {
            WardConsumptionTracker::where('ward_stock_id', $this->ward_stock_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'surgery' => DB::raw("surgery + {$this->non_specific_charge}")
                ]);
        } else if ($this->tscode == 'GYNE') {
            WardConsumptionTracker::where('ward_stock_id', $this->ward_stock_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'obgyne' => DB::raw("obgyne + {$this->non_specific_charge}")
                ]);
        } else if ($this->tscode == 'ORTHO') {
            WardConsumptionTracker::where('ward_stock_id', $this->ward_stock_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'ortho' => DB::raw("ortho + {$this->non_specific_charge}")
                ]);
        } else if ($this->tscode == 'PEDIA') {
            WardConsumptionTracker::where('ward_stock_id', $this->ward_stock_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'pedia' => DB::raw("pedia + {$this->non_specific_charge}")
                ]);
        } else if ($this->tscode == 'OPHTH') {
            WardConsumptionTracker::where('ward_stock_id', $this->ward_stock_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'optha' => DB::raw("optha + {$this->non_specific_charge}")
                ]);
        } else if ($this->tscode == 'ENT') {
            WardConsumptionTracker::where('ward_stock_id', $this->ward_stock_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'ent' => DB::raw("ent + {$this->non_specific_charge}")
                ]);
        } else {
            WardConsumptionTracker::where('ward_stock_id', $this->ward_stock_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'non_specific_charge' => DB::raw("non_specific_charge + {$this->non_specific_charge}")
                ]);
        }
    }

    public function failed(\Throwable $e)
    {
        WardConsumptionTracker::where('ward_stock_id', $this->ward_stock_id)
            ->latest() // Orders by created_at DESC to get the most recent row
            ->first()
            ->update([
                'charged_qty' => DB::raw("charged_qty + {$this->non_specific_charge}")
            ]);
    }
}
