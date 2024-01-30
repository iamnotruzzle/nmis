<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientChargeReturnLogs extends Model
{
    use HasFactory;

    protected $table = 'csrw_patient_charge_return_logs';

    protected $fillable = [
        'enccode',
        'location',
        'hpercode',
        'itemcode',
        'returned_qty',
        'entry_by',
    ];
}
