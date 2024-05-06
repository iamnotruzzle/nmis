<?php

namespace App\Models\Tanks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hdmhdrprice extends Model
{
    use HasFactory;

    protected $table = 'hdmhdrprice';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'dmdcomb',
        'dmdctr',
        'dmhdrsub',
        'dmduprice',
        'unitcode',
        'dmdrem',
        'dmdprdte',
        'dmselprice',
        'stockbal',
        'expdate',
        'stock_id',
    ];
}
