<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardStocks extends Model
{
    use HasFactory;

    protected $table = 'csrw_wards_stocks';

    protected $fillable = [
        'id',
        'request_stock_id',
        'stock_id',
        'location',
        'cl2comb',
        'quantity',
        'manufactured_date',
        'delivered_date',
        'expiration_date',
        'received_date',
    ];
}
