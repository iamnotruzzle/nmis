<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsrItemConversion extends Model
{
    use HasFactory;

    protected $table = 'csrw_csr_item_conversion';

    protected $fillable = [
        'csr_stock_id',
        'ris_no',
        'cl2comb_before',
        'uomcode_before',
        'quantity_before',
        'cl2comb_after',
        'uomcode_after',
        'quantity_after',
        'brand',
        'suppcode',
        'manufactured_date',
        'delivered_date',
        'expiration_date',
    ];
}
