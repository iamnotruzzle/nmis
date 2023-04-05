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
        'selling_price',
        'entry_by',
        'created_at'
    ];

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'entry_by');
    }
}
