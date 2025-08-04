<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\LocationStockBalanceDateLogs;

class TransactionService
{
    public static function getTransactionData($authCode)
    {
        $cacheKey = "transaction_data_ward_{$authCode}";

        return Cache::remember($cacheKey, 6600, function () use ($authCode) {
            $latestDateLog = LocationStockBalanceDateLogs::where('wardcode', $authCode)
                ->latest('created_at')->first();

            $canTransact = null;
            if ($latestDateLog == null) {
                $canTransact = false;
            } else if ($latestDateLog != null && $latestDateLog->end_bal_created_at != null) {
                $canTransact = false;
            } else {
                $canTransact = true;
            }

            return [
                'latestDateLog' => $latestDateLog,
                'canTransact' => $canTransact
            ];
        });
    }

    public static function canTransact($authCode)
    {
        $data = self::getTransactionData($authCode);
        return $data['canTransact'];
    }

    public static function getLatestDateLog($authCode)
    {
        $data = self::getTransactionData($authCode);
        return $data['latestDateLog'];
    }
}
