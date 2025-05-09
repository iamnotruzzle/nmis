<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardsReOrderLevel extends Model
{
    use HasFactory;

    protected $table = 'csrw_wards_stock_level';

    protected $fillable = [
        'cl2comb',
        'reorder_point',
        'reorder_quantity',
        'status',
        'wardcode',
        'created_by',
        'updated_by',
    ];
}
