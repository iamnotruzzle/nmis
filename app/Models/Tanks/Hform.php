<?php

namespace App\Models\Tanks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hform extends Model
{
    use HasFactory;

    protected $table = 'hform';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'formcode',
        'formdesc',
        'formstat',
        'formlock',
        'updsw',
        'datemod',
        'entryby',
    ];
}
