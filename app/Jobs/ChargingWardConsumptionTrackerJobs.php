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
    public $tscode;

    public function __construct($ward_stocks_id, $quantity, $tscode)
    {
        $this->ward_stocks_id = $ward_stocks_id;
        $this->quantity = $quantity;
        $this->tscode = $tscode;
    }

    public function handle()
    {
        if ($this->tscode == 'SURG') {
            WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'surgery' => DB::raw("surgery + {$this->quantity}")
                ]);
        } else if ($this->tscode == 'GYNE') {
            WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'obgyne' => DB::raw("obgyne + {$this->quantity}")
                ]);
        } else if ($this->tscode == 'ORTHO') {
            WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'ortho' => DB::raw("ortho + {$this->quantity}")
                ]);
        } else if ($this->tscode == 'PEDIA') {
            WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'pedia' => DB::raw("pedia + {$this->quantity}")
                ]);
        } else if ($this->tscode == 'OPHTH') {
            WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'optha' => DB::raw("optha + {$this->quantity}")
                ]);
        } else if ($this->tscode == 'ENT') {
            WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'ent' => DB::raw("ent + {$this->quantity}")
                ]);
        } else {
            WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
                ->latest() // Orders by created_at DESC to get the most recent row
                ->first()
                ->update([
                    'charged_qty' => DB::raw("charged_qty + {$this->quantity}")
                ]);
        }
    }

    public function failed(\Throwable $e)
    {
        WardConsumptionTracker::where('wards_stocks_id', $this->ward_stocks_id)
            ->latest() // Orders by created_at DESC to get the most recent row
            ->first()
            ->update([
                'charged_qty' => DB::raw("charged_qty + {$this->quantity}")
            ]);
    }
}
