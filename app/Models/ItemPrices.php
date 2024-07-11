<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPrices extends Model
{
    use HasFactory;

    protected $table = 'csrw_item_prices';

    protected $fillable = [
        'id',
        'cl2comb',
        'price_per_unit',
        'entry_by',
        'created_at',
        'acquisition_price',
        'hospital_price',
        'ris_no',
        'acquisition_price',
        'hospital_price',
        'price_per_unit',
        'item_conversion_id'
    ];

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'entry_by');
    }
}
