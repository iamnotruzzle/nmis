<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnedItems extends Model
{
    use HasFactory;

    protected $table = 'csrw_csr_returned_items';
    // protected $primaryKey = 'rmintkey';
    // public $incrementing = false;
    // // declare primary as string
    // protected $keyType = 'string';

    protected $fillable = [
        'item_conversion_id',
        'csr_stock_id',
        'ris_no',
        'cl2comb',
        'quantity',
        'from',
        'returned_by',
        'remarks',
    ];
}
