<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WardsStocksMedSupp extends Model
{
    use HasFactory;

    protected $table = 'csrw_wards_stocks_med_supp';

    protected $fillable = [
        'id',
        'request_stocks_id',
        'request_stocks_detail_id',
        'stock_id',
        'location',
        'cl2comb',
        'uomcode',
        'brand',
        'chrgcode',
        'quantity',
        'converted_from_ward_stock_id',
        'from',
        'is_converted',
        'manufactured_date',
        'delivered_date',
        'expiration_date',
    ];

    public function request_stocks()
    {
        return $this->hasOne(RequestStocks::class, 'id', 'request_stocks_id');
    }

    public function request_stocks_details()
    {
        return $this->hasOne(RequestStocksDetails::class, 'request_stocks_detail_id', 'id');
    }

    public function stocks_details()
    {
        return $this->hasOne(CsrStocksMedicalSupplies::class, 'stock_id', 'id');
    }

    public function location()
    {
        return $this->hasOne(Location::class, 'location', 'wardcode');
    }

    public function item_details()
    {
        return $this->hasOne(Item::class, 'cl2comb', 'cl2comb')->with('unit:uomcode,uomdesc');
    }

    public function prices()
    {
        return $this->hasMany(ItemPrices::class, 'cl2comb', 'cl2comb')->orderBy('created_at', 'DESC');
    }

    public function brand_details()
    {
        return $this->hasOne(Brand::class, 'id', 'brand');
    }

    public function typeOfCharge()
    {
        // return $this->hasOne(WardsStocks::class, 'stock_id', 'id')->with('item_details:cl2comb,cl2desc');
        return $this->hasOne(TypeOfCharge::class, 'chrgcode', 'chrgcode');
    }

    public function transferredStock()
    {
        return $this->hasMany(WardTransferStock::class, 'ward_stock_id', 'id')->orderBy('created_at', 'DESC');
    }

    public function unit_of_measurement()
    {
        return $this->hasOne(UnitOfMeasurement::class, 'uomcode', 'uomcode');
    }
}
