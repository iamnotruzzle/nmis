<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestTankStocksDetails extends Model
{
    use HasFactory;

    protected $table = 'csrw_request_tank_stocks_details';

    protected $fillable = [
        'id',
        'request_stocks_id',
        'itemcode',
        'requested_qty',
        'approved_qty',
        'remarks',
    ];

    public function request_stocks()
    {
        return $this->hasOne(RequestTankStocks::class, 'request_stocks_id', 'id');
    }

    // public function item_details()
    // {
    //     return $this->hasOne(Item::class, 'cl2comb', 'cl2comb');
    // }

    // public function stocks()
    // {
    //     return $this->hasMany(CsrStocks::class, 'cl2comb', 'cl2comb')->with(['ward_stocks'])
    //         ->whereDate('expiration_date', '>', Carbon::today());
    // }
}
