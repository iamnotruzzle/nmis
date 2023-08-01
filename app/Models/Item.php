<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $table = 'hclass2';
    // protected $primaryKey = 'cl2comb';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'cl2comb',
        'cl1comb',
        'cl2code',
        'stkno',
        'cl2desc',
        'cl2retprc',
        'uomcode',
        'cl2dteas',
        'cl2stat',
        'cl2lock',
        'cl2upsw',
        'cl2dtmd',
        'curcode',
        'cl2purp',
        'curcode1',
        'uomcode1',
        'cl2ctr',
        'brandname',
        'stockbal',
        'pharmaceutical',
        'baldteasof',
        'begbal',
        'lot_no',
        'barcode',
        'rpoint',
    ];

    public function category()
    {
        return $this->hasOne(Category::class, 'cl1comb', 'cl1comb');
    }

    // medical supplies unit
    public function unit()
    {
        return $this->hasOne(UnitOfMeasurement::class, 'uomcode', 'uomcode');
    }

    public function prices()
    {
        return $this->hasMany(ItemPrices::class, 'cl2comb', 'cl2comb')->orderBy('created_at', 'DESC');
    }
}
