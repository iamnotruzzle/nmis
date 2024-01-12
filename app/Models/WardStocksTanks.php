<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardStocksTanks extends Model
{
    use HasFactory;

    protected $table = 'csrw_wards_stocks_tanks';

    protected $fillable = [
        'id',
        'request_stocks_id',
        'request_stocks_detail_id',
        'itemcode',
        'uomcode',
        'quantity',
        'location',
    ];

    public function request_stocks()
    {
        return $this->hasOne(RequestTankStocks::class, 'id', 'request_stocks_id');
    }

    public function request_stocks_details()
    {
        return $this->hasOne(RequestTankStocksDetails::class, 'request_stocks_detail_id', 'id');
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'location', 'wardcode');
    }

    public function unit_of_measurement()
    {
        return $this->hasOne(UnitOfMeasurement::class, 'uomcode', 'uomcode');
    }
}
