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
        'reorder_level_qty',
        'created_by',
        'updated_by',
    ];
}
