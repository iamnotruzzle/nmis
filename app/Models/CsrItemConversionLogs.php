<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsrItemConversionLogs extends Model
{
    use HasFactory;

    protected $table = 'csrw_csr_item_conversion_logs';

    protected $fillable = [
        'item_conversion_id',
        'csr_stock_id',
        'ris_no',
        'chrgcode',
        'cl2comb_before',
        'quantity_before',
        'cl2comb_after',
        'prev_qty',
        'new_qty',
        'suppcode',
        'manufactured_date',
        'delivered_date',
        'expiration_date',
        'action',
        'remarks',
        'converted_by',
        'updated_by',
    ];
}
