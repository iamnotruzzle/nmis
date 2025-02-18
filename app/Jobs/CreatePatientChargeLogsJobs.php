<?php

namespace App\Jobs;

use App\Models\PatientCharge;
use App\Models\PatientChargeLogs;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatePatientChargeLogsJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $enccode;
    public $acctno;
    public $ward_stocks_id;
    public $itemcode;
    public $from;
    public $manufactured_date;
    public $delivery_date;
    public $expiration_date;
    public $quantity;
    public $price_per_piece;
    public $price_total;
    public $pcchrgdte; // charge date
    public $entry_at;
    public $entry_by;
    public $tscode;
    public $pcchrgcod;


    public function __construct(
        $enccode,
        $acctno,
        $ward_stocks_id,
        $itemcode,
        $from,
        $manufactured_date,
        $delivery_date,
        $expiration_date,
        $quantity,
        $price_per_piece,
        $price_total,
        $pcchrgdte, // charge date
        $entry_at,
        $entry_by,
        $tscode,
        $pcchrgcod
    ) {
        $this->enccode = $enccode;
        $this->acctno = $acctno;
        $this->ward_stocks_id = $ward_stocks_id;
        $this->itemcode = $itemcode;
        $this->from = $from;
        $this->manufactured_date = $manufactured_date;
        $this->delivery_date = $delivery_date;
        $this->expiration_date = $expiration_date;
        $this->quantity = $quantity;
        $this->price_per_piece = $price_per_piece;
        $this->price_total = $price_total;
        $this->pcchrgdte = $pcchrgdte;
        $this->entry_at = $entry_at;
        $this->entry_by = $entry_by;
        $this->tscode = $tscode;
        $this->pcchrgcod = $pcchrgcod;
    }

    public function handle()
    {
        PatientChargeLogs::create([
            'enccode' => $this->enccode,
            'acctno' => $this->acctno,
            'ward_stocks_id' => $this->ward_stocks_id,
            'itemcode' =>  $this->itemcode,
            'from' => $this->from,
            'manufactured_date' =>  $this->manufactured_date == null ? null : Carbon::parse($this->manufactured_date)->format('Y-m-d H:i:s.v'),
            'delivery_date' =>  $this->delivery_date == null ? null : Carbon::parse($this->delivery_date)->format('Y-m-d H:i:s.v'),
            'expiration_date' =>  $this->expiration_date == null ? null : Carbon::parse($this->expiration_date)->format('Y-m-d H:i:s.v'),
            // 'quantity' => $item['qtyToCharge'],
            'quantity' => $this->quantity,
            'price_per_piece' => $this->price_per_piece,
            'price_total' => $this->price_total,
            'pcchrgdte' => $this->pcchrgdte,
            'tscode' => $this->tscode,
            'entry_at' => $this->entry_at,
            'entry_by' => $this->entry_by,
            'pcchrgcod' => $this->pcchrgcod, // charge slip no.
        ]);
    }
}
