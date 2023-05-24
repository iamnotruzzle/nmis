<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientBillLogs extends Model
{
    use HasFactory;

    protected $table = 'csrw_patient_bill_logs';

    protected $fillable = [
        'id',
        'enccode',
        'acctno',
        'ward_stocks_id',
        'itemcode',
        'batch_no',
        'manufactured_date',
        'delivery_date',
        'expiration_date',
        'quantity',
        'price_per_piece',
        'price_total',
        'charge_date',
        'entry_at',
        'entry_by',
    ];
}
