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
        'request_stock_id', // from csrw_request_stocks
        'stock_id', // from csr_stocks
        'location',
        'cl2comb',
        'quantity',
        'manufactured_date',  // from csr_stocks
        'delivered_date',  // from csr_stocks
        'expiration_date',  // from csr_stocks
        'received_date',
    ];

    public function requestStockDetail()
    {
        return $this->hasOne(RequestStock::class, 'id', 'request_stock_id');
    }

    public function stockDetail()
    {
        return $this->hasOne(CsrStocks::class, 'id', 'stock_id');
    }
}
