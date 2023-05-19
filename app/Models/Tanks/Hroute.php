<?php

namespace App\Models\Tanks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hroute extends Model
{
    use HasFactory;

    protected $table = 'hroute';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'rtecode',
        'rtedesc',
        'rtestat',
        'rtelock',
        'updsw',
        'datemod',
    ];
}
