<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStock extends Model
{
    use HasFactory;

    protected $table = 'csrw_request_stocks';

    protected $fillable = [
        'id',
        'cl2comb',
        'requested_qty',
        'approved_qty',
        'status',
        'requested_by',
        'requested_at', // ward location
        'approved_by',
    ];


    public function itemDetail()
    {
        return $this->hasOne(Item::class, 'cl2comb', 'cl2comb')->select(['cl2comb', 'cl2desc']);
    }

    public function requested_by_details()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'requested_by')->select(['employeeid', 'firstname', 'middlename', 'lastname']);
    }

    public function requested_at_details()
    {
        return $this->hasOne(Location::class, 'wardcode', 'requested_at')->select(['wardcode', 'wardname']);
    }

    public function approved_by_details()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'approved_by')->select(['employeeid', 'firstname', 'middlename', 'lastname']);
    }
}
