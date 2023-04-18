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
        'quantity',
        'manufactured_date',
        'delivered_date',
        'expiration_date',
    ];

    public function itemDetail()
    {
        return $this->hasOne(Item::class, 'cl2comb', 'cl2comb');
    }
}