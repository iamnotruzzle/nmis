<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestTankStocks extends Model
{
    protected $table = 'csrw_request_tank_stocks';

    protected $fillable = [
        'id',
        'location',
        'status',
        'requested_by',
        'approved_by',
        'received_date',
    ];

    public function requested_at_details()
    {
        return $this->hasOne(Location::class, 'wardcode', 'location');
    }

    public function requested_by_details()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'requested_by')->with('user_account:id,employeeid,image');
    }

    public function approved_by_details()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'approved_by')->with('user_account:id,employeeid,image');
    }

    public function request_stocks_details()
    {
        return $this->hasMany(RequestTankStocksDetails::class, 'request_stocks_id', 'id');
    }
}