<?php

namespace App\Models\Tanks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hdmhdr extends Model
{
    use HasFactory;

    protected $table = 'hdmhdr';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'dmdcomb',
        'dmdctr',
        'dmdnost',
        'strecode',
        'formcode',
        'rtecode',
        'dmdpndf',
        'dmdstat',
        'dmdlock',
        'dmdupsw',
        'dmddtmd',
        'brandname',
        'atcode',
        'dmdnnostp',
        'dmdclaimno',
        'techspec',
        'grpcode',
        'dmdrem',
        'dmdrxot',
        'dmdclmuom',
        'dmdstco',
        'dmdedl',
        'lbscode',
        'stockbal',
        'baldteasof',
        'begbal',
        'hprodid',
        'barcode',
        'lot_no',
        'tic',
        'packcode',
        'saltcode',
    ];
}