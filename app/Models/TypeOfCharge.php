<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeOfCharge extends Model
{
    use HasFactory;

    protected $table = 'hcharge';
    protected $primaryKey = 'chrgcode';
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'chrgcode',
        'chrgdesc',
        'bentypcode',
        'chrgstat',
        'chrglock',
        'datemod',
        'updsw',
        'chrgtable',
        'acctcode',
        'chargseq',
        'costcenter',
        'income',
        'percentage',
        'mssfulldisc',
        'payseq',
        'uacscode',
        'acctdesc',
    ];
}
