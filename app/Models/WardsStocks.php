<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardsStocks extends Model
{
    use HasFactory;

    protected $table = 'csrw_wards_stocks';

    protected $fillable = [
        'id',
        'request_stocks_id',
        'request_stocks_detail_id',
        'stock_id',
        'location',
        'cl2comb',
        'quantity',
        'manufactured_date',
        'delivered_date',
        'expiration_date',
    ];

    public function request_stocks()
    {
        return $this->hasOne(RequestStocks::class, 'request_stocks_id', 'id');
    }

    public function request_stocks_details()
    {
        return $this->hasOne(RequestStocksDetails::class, 'request_stocks_detail_id', 'id');
    }

    public function stocks_details()
    {
        return $this->hasOne(CsrStocks::class, 'stock_id', 'id');
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'location', 'wardcode');
    }
}
