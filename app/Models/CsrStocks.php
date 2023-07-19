<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsrStocks extends Model
{
    use HasFactory;

    protected $table = 'csrw_csr_stocks';
    // protected $primaryKey = 'cl1comb';
    // public $incrementing = false;
    // declare primary as string
    // protected $keyType = 'string';
    // public $timestamps = false;

    protected $fillable = [
        'id',
        'batch_no',
        'cl2comb',
        'brand',
        'chrgcode',
        'quantity',
        'manufactured_date',
        'delivered_date',
        'expiration_date',
        'deployed' // Y or N
    ];

    public function itemDetail()
    {
        return $this->hasOne(Item::class, 'cl2comb', 'cl2comb');
    }

    public function brandDetail()
    {
        return $this->hasOne(Brand::class, 'id', 'brand');
    }

    public function stockLogs()
    {
        return $this->hasOne(CsrStocksLogs::class, 'id', 'csrw_stocks_id');
    }

    public function ward_stocks()
    {
        // return $this->hasOne(WardsStocks::class, 'stock_id', 'id')->with('item_details:cl2comb,cl2desc');
        return $this->hasOne(WardsStocks::class, 'stock_id', 'id');
    }

    public function typeOfCharge()
    {
        // return $this->hasOne(WardsStocks::class, 'stock_id', 'id')->with('item_details:cl2comb,cl2desc');
        return $this->hasOne(TypeOfCharge::class, 'chrgcode', 'chrgcode');
    }
}
