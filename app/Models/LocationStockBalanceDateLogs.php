<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationStockBalanceDateLogs extends Model
{
    use HasFactory;

    protected $table = 'csrw_stock_bal_date_logs';

    protected $fillable = [
        'id',
        'wardcode',
        'beg_bal_created_at',
        'end_bal_created_at',
        'beg_bal_declared_by',
        'end_bal_declared_by',
    ];
}
