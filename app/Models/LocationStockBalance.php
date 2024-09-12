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
        'beginning_balance',
        'entry_by',
        'updated_by',
        // 'ward_stock_id',
        'end_bal_created_at',
        'beg_bal_created_at',
        'ris_no',
    ];

    public function item()
    {
        return $this->hasOne(Item::class, 'cl2comb', 'cl2comb');
    }

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
