<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationTankStockBalance extends Model
{
    use HasFactory;

    protected $table = 'csrw_location_tank_stock_balance';

    protected $fillable = [
        'id',
        'location',
        'itemcode',
        'ending_balance',
        'beginning_balance',
        'entry_by',
        'updated_by',
    ];

    public function entry_by()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'entry_by');
    }

    public function updated_by()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'updated_by');
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'wardcode', 'location');
    }
}
