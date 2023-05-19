<?php

namespace App\Models\Tanks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hstre extends Model
{
    use HasFactory;

    protected $table = 'hstre';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'strecode',
        'stredesc',
        'strestat',
        'strelock',
        'updsw',
        'datemod',
        'entryby',
    ];
}
