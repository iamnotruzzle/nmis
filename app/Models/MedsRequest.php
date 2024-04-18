<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedsRequest extends Model
{
    use HasFactory;

    protected $table = 'csrw_meds_request';

    protected $fillable = [
        'reference_id',
        'dmdprdte',
        'dmdcomb',
        'dmdctr',
        'selling_price',
        'requested_qty',
        'approved_qty',
        'expiration_date',
        'wardcode',
        'status',
        'remarks',
    ];
}
