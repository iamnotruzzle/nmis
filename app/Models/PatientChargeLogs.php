<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientChargeLogs extends Model
{
    use Compoships;
    use HasFactory;

    protected $table = 'csrw_patient_charge_logs';
    // protected $primaryKey = ['id', 'enccode', 'itemcode', 'pcchrgdte'];
    // // protected $primaryKey = null;
    // public $incrementing = false;

    // // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'enccode',
        'acctno',
        'ward_stocks_id',
        'brand',
        'itemcode',
        'manufactured_date',
        'delivery_date',
        'expiration_date',
        'quantity',
        'price_per_piece',
        'price_total',
        'pcchrgdte', // charge date
        'entry_at',
        'entry_by',
    ];

    public function patientCharge()
    {
        return $this->belongsTo(PatientCharge::class, ['enccode', 'pcchrgdte', 'itemcode'], ['enccode', 'pcchrgdte', 'itemcode']);
    }

    public function brand_details()
    {
        return $this->hasOne(Brand::class, 'id', 'brand');
    }
}
