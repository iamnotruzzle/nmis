<?php

namespace App\Models;


use App\Models\Item;
use App\Models\Tanks\Hdmhdr;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PatientCharge extends Model
{
    use Compoships;
    use HasFactory;

    protected $table = 'hpatchrg';
    // protected $primaryKey = ['enccode', 'itemcode', 'pcchrgdte'];
    // protected $primaryKey = null;
    // public $incrementing = false;

    // // declare primary as string
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'enccode',
        'hpercode',
        'pcchrgcod',
        'pcchrgdte',
        'chargcode',
        'uomcode',
        'pchrgqty',
        'pchrgup',
        'pcchrgamt',
        'pcstat',
        'pclock',
        'datemod',
        'updsw',
        'confdl',
        'srcchrg',
        'pcdish',
        'acctno',
        'itemcode',
        'entryby',
        'orinclst',
        'compense',
        'proccode',
        'discount',
        'disamt',
        'discbal',
        'phicamt',
        'rvscode',
        'licno',
        'hpatkey',
        'time_frequency',
        'unit_frequency',
        'qtyintake',
        'uomintake',
    ];

    public function typeOfCharge()
    {
        return $this->hasOne(TypeOfCharge::class, 'chrgcode', 'chargcode');
    }

    public function item()
    {
        // ORIG code
        return $this->hasOne(Item::class, 'cl2comb', 'itemcode')->with(['category:cl1comb,cl1desc,cl1stat', 'unit:uomcode,uomdesc,uomstat']);
    }

    public function misc()
    {
        // return $this->hasOne(Miscellaneous::class, 'hmcode', 'itemcode')->with(['unit']);
        return $this->hasOne(Miscellaneous::class, 'hmcode', 'itemcode');
    }

    public function tank()
    {
        $h = new Hdmhdr();

        $dmdcomb = $h->dmdcomb . '' . $h->dmdctr;

        // ['dmdcomb', 'dmdctr'],
        return $this->hasOne(Hdmhdr::class, $h->dmdcomb . '' . $h->dmdctr, 'itemcode')->with(['hdruggrp', 'hstre', 'hform', 'hroute']);
    }

    public function patientChargeLogs()
    {
        return $this->hasMany(PatientChargeLogs::class, ['enccode', 'pcchrgdte', 'itemcode'], ['enccode', 'pcchrgdte', 'itemcode'])->with('brand_details:id,name');
    }
}
