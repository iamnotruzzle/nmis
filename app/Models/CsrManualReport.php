<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsrManualReport extends Model
{
    use HasFactory;

    protected $table = 'csrw_csr_manual_report';
    // protected $primaryKey = 'cl1comb';
    // public $incrementing = false;
    // declare primary as string
    // protected $keyType = 'string';
    // public $timestamps = false;

    protected $fillable = [
        'id',
        'cl2comb',
        'uomcode',
        'unit_cost',
        'csr_beg_bal_quantity',
        'csr_beg_bal_total_cost',
        'wards_beg_bal_quantity',
        'wards_beg_bal_total_cost',
        'total_beg_bal_total_quantity',
        'total_beg_bal_total_cost',
        'supp_issued_to_wards_total_quantity',
        'supp_issued_to_wards_total_cost',
        'consumption_quantity',
        'consumption_total_cost',
        'csr_end_bal_quantity',
        'csr_end_bal_total_cost',
        'wards_end_bal_quantity',
        'wards_end_bal_total_cost',
        'total_end_bal_total_quantity',
        'total_end_bal_total_cost',
        'entry_by',
        'updated_by',
    ];

    public function item_description()
    {
        return $this->hasOne(Item::class, 'cl2comb', 'cl2comb');
    }

    public function unit()
    {
        return $this->hasOne(UnitOfMeasurement::class, 'uomcode', 'uomcode');
    }
}
