<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardStocksTanksLogs extends Model
{
    use HasFactory;

    protected $table = 'csrw_wards_stocks_tanks_supp_logs';

    protected $fillable = [
        'id',
        'request_stocks_id',
        'request_stocks_detail_id',
        'stock_id',
        'itemcode',
        'location',
        'prev_qty',
        'new_qty',
        'action',
        'remarks',
        'converted_from_ward_stock_id',
        'is_converted',
        'entry_by',
    ];
}
