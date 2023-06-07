<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsrStocksLogs extends Model
{
    use HasFactory;
    protected $table = 'csrw_csr_stocks';

    protected $fillable = [
        'id',
        'csrw_stocks_id',
        'previous_qty',
        'new_qty',
        'action',
        'remarks',
        'entry_by',
    ];
}
