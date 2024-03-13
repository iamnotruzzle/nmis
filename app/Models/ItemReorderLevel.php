<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReorderLevel extends Model
{
    use HasFactory;

    protected $table = 'csrw_item_reorder_level';

    protected $fillable = [
        'cl2comb',
        'normal_stock',
        'alert_stock',
        'critical_stock',
        'entry_by',
        'location',
    ];
}
