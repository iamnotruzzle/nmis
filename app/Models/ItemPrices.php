<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPrices extends Model
{
    use HasFactory;

    protected $table = 'csrw_item_prices';
    protected $primaryKey = 'cl2comb';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'cl2comb',
        'uomcode',
        'selling_price',
        'entry_by',
        'created_at'
    ];
}
