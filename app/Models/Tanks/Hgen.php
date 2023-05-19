<?php

namespace App\Models\Tanks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hgen extends Model
{
    use HasFactory;

    protected $table = 'hgen';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'gencode',
        'gendesc',
        'genstat',
        'genlock',
        'updsw',
        'datemod',
        'entryby',
        'edpms_code',
        'edpms_desc',
        'rationale',
        'monitor',
        'interactions',
    ];
}
