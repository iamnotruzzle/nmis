<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientHeightWeight extends Model
{
    use HasFactory;

    // table to get the weight and height of the patient
    protected $table = 'hvsothr';
    protected $primaryKey = 'enccode';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'enccode',
        'hpercode',
        'vsheight',
        'vsweight'
    ];
}
