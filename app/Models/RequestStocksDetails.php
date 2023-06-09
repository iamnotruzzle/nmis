<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStocksDetails extends Model
{
    use HasFactory;

    protected $table = 'csrw_request_stocks_details';

    protected $fillable = [
        'id',
        'request_stocks_id',
        'cl2comb',
        'requested_qty',
        'approved_qty',
    ];

    public function request_stocks()
    {
        return $this->hasOne(RequestStocks::class, 'request_stocks_id', 'id');
    }

    public function item_details()
    {
        return $this->hasOne(Item::class, 'cl2comb', 'cl2comb');
    }

    public function stocks()
    {
        return $this->hasMany(CsrStocks::class, 'cl2comb', 'cl2comb')->with(['brandDetail:id,name', 'ward_stocks']);
    }

    // public function ward_stocks()
    // {
    //     return $this->hasMany(WardsStocks::class, 'request_stocks_detail_id', 'id')->with('item_details:cl2comb,cl2desc');
    // }
}
