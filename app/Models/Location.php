<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $table = 'hward';
    protected $primaryKey = 'wardcode';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'wardcode',
        'wardname',
        'wclcode',
        'wardrmno',
        'wardstat',
        'wardlock',
        'wvacov',
        'datemod',
        'updsw',
        'tscode',
        'noroom',
        'sex',
        'tacode',
        'dietLock',
        'dietSeenId'
    ];
}
