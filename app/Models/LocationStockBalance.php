<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationStockBalance extends Model
{
    use HasFactory;

    protected $table = 'csrw_location_stock_balance';

    protected $fillable = [
        'id',
        'location',
        'cl2comb',
        'ending_balance',
        'entry_by',
    ];

    public function item()
    {
        return $this->hasOne(Item::class, 'cl2comb', 'cl2comb');
    }

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'entry_by');
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'wardcode', 'location');
    }
}
