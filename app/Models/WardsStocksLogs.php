<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardsStocksLogs extends Model
{
    use HasFactory;
    protected $table = 'csrw_wards_stocks_logs';

    protected $fillable = [
        'id',
        'request_stocks_id',
        'request_stocks_detail_id',
        'stock_id',
        'ris_no',
        'location',
        'cl2comb',
        'uomcode',
        'chrgcode',
        'prev_qty',
        'new_qty',
        'manufactured_date',
        'delivered_date',
        'expiration_date',
        'action',
        'remarks',
        'entry_by',
        'is_consumable',
        'average',
        'total_usage',
    ];

    public function unit_of_measurement()
    {
        return $this->hasOne(UnitOfMeasurement::class, 'uomcode', 'uomcode');
    }
}
