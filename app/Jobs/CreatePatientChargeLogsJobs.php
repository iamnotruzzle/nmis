<?php

namespace App\Jobs;

use App\Events\ChargeLogsProcessed;
use App\Models\PatientChargeLogs;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreatePatientChargeLogsJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // jobs
    // public $tries = 5;

    public $chargeLogs;
    // public $acctno;
    // public $ward_stocks_id;
    // public $itemcode;
    // public $from;
    // public $manufactured_date;
    // public $delivery_date;
    // public $expiration_date;
    // public $quantity;
    // public $price_per_piece;
    // public $price_total;
    // public $pcchrgdte; // charge date
    // public $entry_at;
    // public $entry_by;
    // public $tscode;
    // public $pcchrgcod;


    public function __construct(
        // $enccode,
        // $acctno,
        // $ward_stocks_id,
        // $itemcode,
        // $from,
        // $manufactured_date,
        // $delivery_date,
        // $expiration_date,
        // $quantity,
        // $price_per_piece,
        // $price_total,
        // $pcchrgdte, // charge date
        // $entry_at,
        // $entry_by,
        // $tscode,
        // $pcchrgcod
        array $chargeLogs
    ) {
        // $this->enccode = $enccode;
        // $this->acctno = $acctno;
        // $this->ward_stocks_id = $ward_stocks_id;
        // $this->itemcode = $itemcode;
        // $this->from = $from;
        // $this->manufactured_date = $manufactured_date;
        // $this->delivery_date = $delivery_date;
        // $this->expiration_date = $expiration_date;
        // $this->quantity = $quantity;
        // $this->price_per_piece = $price_per_piece;
        // $this->price_total = $price_total;
        // $this->pcchrgdte = $pcchrgdte;
        // $this->entry_at = $entry_at;
        // $this->entry_by = $entry_by;
        // $this->tscode = $tscode;
        // $this->pcchrgcod = $pcchrgcod;
        $this->chargeLogs = $chargeLogs;
    }

    public function handle()
    {
        // PatientChargeLogs::create([
        //     'enccode' => $this->enccode,
        //     'acctno' => $this->acctno,
        //     'ward_stocks_id' => $this->ward_stocks_id,
        //     'itemcode' =>  $this->itemcode,
        //     'from' => $this->from,
        //     'manufactured_date' =>  $this->manufactured_date == null ? null : Carbon::parse($this->manufactured_date)->format('Y-m-d H:i:s.v'),
        //     'delivery_date' =>  $this->delivery_date == null ? null : Carbon::parse($this->delivery_date)->format('Y-m-d H:i:s.v'),
        //     'expiration_date' =>  $this->expiration_date == null ? null : Carbon::parse($this->expiration_date)->format('Y-m-d H:i:s.v'),
        //     // 'quantity' => $item['qtyToCharge'],
        //     'quantity' => $this->quantity,
        //     'price_per_piece' => $this->price_per_piece,
        //     'price_total' => $this->price_total,
        //     'pcchrgdte' => $this->pcchrgdte,
        //     'tscode' => $this->tscode,
        //     'entry_at' => $this->entry_at,
        //     'entry_by' => $this->entry_by,
        //     'pcchrgcod' => $this->pcchrgcod, // charge slip no.
        // ]);

        foreach ($this->chargeLogs as $logData) {
            PatientChargeLogs::create(
                [
                    'enccode' => $logData['enccode'],
                    'acctno' => $logData['acctno'],
                    'ward_stocks_id' => $logData['ward_stocks_id'],
                    'itemcode' => $logData['itemcode'],
                    'from' => $logData['from'],
                    'manufactured_date' => $logData['manufactured_date'],
                    'delivery_date' => $logData['delivery_date'],
                    'expiration_date' => $logData['expiration_date'],
                    'quantity' => $logData['quantity'],
                    'price_per_piece' => $logData['price_per_piece'],
                    'price_total' => $logData['price_total'],
                    'pcchrgdte' => $logData['pcchrgdte'],
                    'entry_at' => $logData['entry_at'],
                    'entry_by' => $logData['entry_by'],
                    'pcchrgcod' => $logData['pcchrgcod'],
                    'tscode' => $logData['tscode'],
                ]
            );
        }


        event(new ChargeLogsProcessed([
            'message' => "Patient charge logs processed."
        ]));


        // // testing purposes
        // throw new \Exception('Failed!');
    }

    // if the job failed after trying execute this
    public function failed(\Throwable $e)
    {
        // PatientChargeLogs::create([
        //     'enccode' => $this->enccode,
        //     'acctno' => $this->acctno,
        //     'ward_stocks_id' => $this->ward_stocks_id,
        //     'itemcode' =>  $this->itemcode,
        //     'from' => $this->from,
        //     'manufactured_date' =>  $this->manufactured_date == null ? null : Carbon::parse($this->manufactured_date)->format('Y-m-d H:i:s.v'),
        //     'delivery_date' =>  $this->delivery_date == null ? null : Carbon::parse($this->delivery_date)->format('Y-m-d H:i:s.v'),
        //     'expiration_date' =>  $this->expiration_date == null ? null : Carbon::parse($this->expiration_date)->format('Y-m-d H:i:s.v'),
        //     // 'quantity' => $item['qtyToCharge'],
        //     'quantity' => $this->quantity,
        //     'price_per_piece' => $this->price_per_piece,
        //     'price_total' => $this->price_total,
        //     'pcchrgdte' => $this->pcchrgdte,
        //     'tscode' => $this->tscode,
        //     'entry_at' => $this->entry_at,
        //     'entry_by' => $this->entry_by,
        //     'pcchrgcod' => $this->pcchrgcod, // charge slip no.
        // ]);

        foreach ($this->chargeLogs as $logData) {
            PatientChargeLogs::create(
                [
                    'enccode' => $logData['enccode'],
                    'acctno' => $logData['acctno'],
                    'ward_stocks_id' => $logData['ward_stocks_id'],
                    'itemcode' => $logData['itemcode'],
                    'from' => $logData['from'],
                    'manufactured_date' => $logData['manufactured_date'],
                    'delivery_date' => $logData['delivery_date'],
                    'expiration_date' => $logData['expiration_date'],
                    'quantity' => $logData['quantity'],
                    'price_per_piece' => $logData['price_per_piece'],
                    'price_total' => $logData['price_total'],
                    'pcchrgdte' => $logData['pcchrgdte'],
                    'entry_at' => $logData['entry_at'],
                    'entry_by' => $logData['entry_by'],
                    'pcchrgcod' => $logData['pcchrgcod'],
                    'tscode' => $logData['tscode'],
                ]
            );
        }

        event(new ChargeLogsProcessed([
            'message' => "Patient charge logs processed."
        ]));
    }
}
