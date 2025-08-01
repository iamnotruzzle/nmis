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
use Illuminate\Support\Facades\Log;

class ChargingWardConsumptionBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $batchData;

    public function __construct(array $batchData)
    {
        $this->batchData = $batchData;
    }

    public function handle()
    {
        $batchSize = count($this->batchData);
        $startTime = microtime(true);

        Log::info("ChargingWardConsumptionBatchJob: Starting batch processing of {$batchSize} items", [
            'job_id' => $this->job->getJobId(),
            'queue' => $this->job->getQueue(),
            'batch_size' => $batchSize
        ]);

        DB::statement('SET LOCK_TIMEOUT 5000'); // 5 second timeout for SQL Server

        $processed = 0;
        $errors = 0;

        try {
            foreach ($this->batchData as $index => $data) {
                try {
                    $this->updateConsumptionTracker(
                        $data['ward_stock_id'],
                        $data['non_specific_charge'],
                        $data['tscode']
                    );
                    $processed++;

                    Log::debug("Processed item {$index}/{$batchSize}", [
                        'ward_stock_id' => $data['ward_stock_id'],
                        'charge' => $data['non_specific_charge'],
                        'tscode' => $data['tscode']
                    ]);
                } catch (\Exception $itemError) {
                    $errors++;
                    Log::error("Failed to process item {$index}/{$batchSize}", [
                        'ward_stock_id' => $data['ward_stock_id'],
                        'error' => $itemError->getMessage(),
                        'tscode' => $data['tscode']
                    ]);

                    // Continue processing other items instead of failing entire batch
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            Log::info("ChargingWardConsumptionBatchJob: Completed batch processing", [
                'job_id' => $this->job->getJobId(),
                'processed' => $processed,
                'errors' => $errors,
                'total' => $batchSize,
                'duration_seconds' => $duration,
                'items_per_second' => $batchSize > 0 ? round($batchSize / $duration, 2) : 0
            ]);

            // Only fail the job if ALL items failed
            if ($errors > 0 && $processed === 0) {
                throw new \Exception("All {$batchSize} items in batch failed to process");
            }
        } catch (\Exception $e) {
            $duration = round(microtime(true) - $startTime, 2);

            Log::error("ChargingWardConsumptionBatchJob: Batch processing failed", [
                'job_id' => $this->job->getJobId(),
                'error' => $e->getMessage(),
                'processed_before_failure' => $processed,
                'total_items' => $batchSize,
                'duration_seconds' => $duration
            ]);

            throw $e;
        }
    }

    private function updateConsumptionTracker($ward_stock_id, $charge, $tscode)
    {
        $startTime = microtime(true);

        try {
            // Optimized query - use primary key instead of created_at
            $record = WardConsumptionTracker::where('ward_stock_id', $ward_stock_id)
                ->orderBy('id', 'DESC')
                ->first();

            if (!$record) {
                Log::warning("WardConsumptionTracker record not found", [
                    'ward_stock_id' => $ward_stock_id,
                    'tscode' => $tscode
                ]);
                return;
            }

            // Use match statement for cleaner code
            $column = match ($tscode) {
                'SURG' => 'surgery',
                'GYNE' => 'obgyne',
                'ORTHO' => 'ortho',
                'PEDIA' => 'pedia',
                'OPHTH' => 'optha',
                'ENT' => 'ent',
                default => 'non_specific_charge'
            };

            $oldValue = $record->$column;

            // Use increment for atomic update
            $record->increment($column, $charge);

            $duration = round((microtime(true) - $startTime) * 1000, 2); // Convert to milliseconds

            Log::debug("WardConsumptionTracker updated successfully", [
                'ward_stock_id' => $ward_stock_id,
                'tscode' => $tscode,
                'column' => $column,
                'old_value' => $oldValue,
                'added_charge' => $charge,
                'new_value' => $oldValue + $charge,
                'query_duration_ms' => $duration
            ]);
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            // Check if it's a timeout error
            $isTimeout = str_contains($e->getMessage(), 'timeout') || str_contains($e->getMessage(), 'lock');

            Log::error("WardConsumptionTracker update failed", [
                'ward_stock_id' => $ward_stock_id,
                'tscode' => $tscode,
                'charge' => $charge,
                'error' => $e->getMessage(),
                'is_timeout' => $isTimeout,
                'query_duration_ms' => $duration
            ]);

            throw $e;
        }
    }
}
