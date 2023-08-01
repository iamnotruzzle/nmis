<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitOfMeasurement extends Model
{
    use HasFactory;

    protected $table = 'huom';
    // protected $primaryKey = 'uomcode';
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'uomcode',
        'uomdesc',
        'uomstat',
        'uomlock',
        'datemod',
        'updsw',
    ];
}
