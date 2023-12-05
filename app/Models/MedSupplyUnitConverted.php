<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedSupplyUnitConverted extends Model
{
    use HasFactory;

    protected $table = 'csrw_med_supply_unit_converted';

    protected $fillable = [
        'ward_med_supp_id',
        'wardcode',
        'orig_cl2comb',
        'orig_uomcode',
        'orig_quantity',
        'converted_cl2comb',
        'converted_uomcode',
        'converted_quantity',
    ];
}
