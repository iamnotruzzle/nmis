<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardStockAdjustment extends Model
{
    use HasFactory;

    public $table = 'csrw_ward_stock_adjustment';

    protected $fillable = [
        'ward_stock_id',
        'cl2comb',
        'quantity_used',
        'previous_quantity',
        'new_quantity',
        'employeeid',
        'tag',
        'remarks',
    ];
}
