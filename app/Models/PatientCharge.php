<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientCharge extends Model
{
    use HasFactory;

    protected $table = 'hpatchrg';
    protected $primaryKey = 'itemcode';
    // protected $primaryKey = null;
    public $incrementing = false;

    // declare primary as string
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
    ];

    public function typeOfCharge()
    {
        return $this->hasOne(TypeOfCharge::class, 'chrgcode', 'chargcode');
    }

    public function item()
    {
        return $this->hasOne(Item::class, 'cl2comb', 'itemcode')->with(['category', 'unit']);
    }

    public function misc()
    {
        return $this->hasOne(Miscellaneous::class, 'hmcode', 'itemcode')->with(['unit']);
    }
}
