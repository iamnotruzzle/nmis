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


    public function hdruggrp()
    {
        return $this->hasOne(Hdruggrp::class, 'grpcode', 'grpcode')->with(['hgen']);
    }

    public function hstre()
    {
        return $this->hasOne(Hstre::class, 'strecode', 'strecode');
    }

    public function hform()
    {
        return $this->hasOne(Hform::class, 'formcode', 'formcode');
    }

    public function hroute()
    {
        return $this->hasOne(Hroute::class, 'rtecode', 'rtecode');
    }
}
