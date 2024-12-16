<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardStockBalanceSnapshot extends Model
{
    use HasFactory;

    protected $table = 'csrw_ward_stock_balance_snapshot';

    protected $fillable = [
        'cl2comb',
        'item_description',
        'unit',
        'unit_cost',
        'beginning_balance',
        'from_csr',
        'from_ward',
        'total_beg_bal',
        'surgery',
        'obgyne',
        'ortho',
        'pedia',
        'ent',
        'total_consumption',
        'total_cons_estimated_cost',
        'transferred_qty',
        'ending_balance',
        'wardcode',
    ];
}
