<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsrStocksLogs extends Model
{
    use HasFactory;
    protected $table = 'csrw_csr_stocks_logs';

    protected $fillable = [
        'id',
        'batch_no',
        'cl2comb',
        'brand',
        'prev_qty',
        'new_qty',
        'manufactured_date',
        'delivered_date',
        'expiration_date',
        'action',
        'remarks',
        'entry_by',
    ];
}
