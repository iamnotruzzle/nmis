<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsrStocksReport extends Model
{
    use HasFactory;

    protected $table = 'csrw_csr_stocks_report';

    protected $fillable = [
        'id',
        'stock_id',
        'cl2comb',
        'qty',
    ];
}
