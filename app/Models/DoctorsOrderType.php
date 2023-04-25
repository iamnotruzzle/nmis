<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorsOrderType extends Model
{
    use HasFactory;

    protected $table = 'hprocm';
    protected $primaryKey = 'proccode';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'proccode',
        'procdesc',
        'optycode',
        'protcode',
        'procuval',
        'procrem',
        'procstat',
        'proclock',
        'costcenter',
    ];
}
