<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardsStocksMedSuppLogs extends Model
{
    use HasFactory;
    protected $table = 'csrw_wards_stocks_med_supp_logs';

    protected $fillable = [
        'id',
        'request_stocks_id',
        'request_stocks_detail_id',
        'stock_id',
        'location',
        'cl2comb',
        'uomcode',
        'brand',
        'chrgcode',
        'prev_qty',
        'new_qty',
        'converted_from_ward_stock_id',
        'manufactured_date',
        'delivered_date',
        'expiration_date',
        'action',
        'remarks',
        'entry_by'
    ];

    public function unit_of_measurement()
    {
        return $this->hasOne(UnitOfMeasurement::class, 'uomcode', 'uomcode');
    }
}
