<?php

namespace App\Models\Tanks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hdruggrp extends Model
{
    use HasFactory;

    protected $table = 'hdruggrp';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'grpcode',
        'grpstat',
        'grplock',
        'grpupsw',
        'grpdtmd',
        'dmcode',
        'dms1key',
        'dms2key',
        'dms3key',
        'dms4key',
        'gencode',
    ];
}
