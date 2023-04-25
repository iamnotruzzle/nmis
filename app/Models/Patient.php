<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $table = 'hperson';
    protected $primarykey = 'hpercode';
    public $incrementing = false;

    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'hpatkey',
        'hpercode',
        'hpatcode',
        'patfirst', // patient first name
        'patmiddle', // patient middle name
        'patlast', // patient last name
        'patsuffix', // patient suffix
        'patbdate', // patient birth date
    ];
}
