<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardsStocksMeds extends Model
{
    use HasFactory;

    protected $table = 'csrw_wards_meds_stocks';

    protected $fillable = [
        'meds_request_id',
        'reference_id',
        'dmdprdte',
        'dmdcomb',
        'dmdctr',
        'fsid',
        'selling_price',
        'quantity',
        'expiration_date',
        'wardcode',
    ];
}
