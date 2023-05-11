<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Miscellaneous extends Model
{
    use HasFactory;

    protected $table = 'hmisc';
    protected $primaryKey = 'hmcode';
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'hmcode',
        'hmdesc',
        'hmdteas',
        'hmamt',
        'hmstat',
        'hmlock',
        'updsw',
        'datemod',
        'uomcode', // UNIT
        'srcchrg',
        'priority',
        'discount'
    ];

    public function unit()
    {
        return $this->hasOne(UnitOfMeasurement::class, 'uomcode', 'uomcode');
    }
}
