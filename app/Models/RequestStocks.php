<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStocks extends Model
{
    use HasFactory;

    protected $table = 'csrw_request_stocks';

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
        // old eloquent where id and image is still an active column
        // return $this->hasOne(UserDetail::class, 'employeeid', 'requested_by')->with('user_account:id,employeeid,image');
        return $this->hasOne(UserDetail::class, 'employeeid', 'requested_by')->with('user_account:employeeid');
    }

    public function approved_by_details()
    {
        // old eloquent where id and image is still an active column
        // return $this->hasOne(UserDetail::class, 'employeeid', 'approved_by')->with('user_account:id,employeeid,image');
        return $this->hasOne(UserDetail::class, 'employeeid', 'approved_by')->with('user_account:employeeid');
    }

    public function request_stocks_details()
    {
        return $this->hasMany(RequestStocksDetails::class, 'request_stocks_id', 'id')->with(['issued_item', 'converted_item']);
    }
}
