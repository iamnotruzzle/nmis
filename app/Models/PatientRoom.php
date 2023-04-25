<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientRoom extends Model
{
    use HasFactory;

    protected $table = 'hpatroom';

    protected $fillable = [
        'enccode',
        'hpercode',
        'wardcode',
        'rmintkey',
        'bdintkey',
        'rmvcode',
        'patrmstat'
    ];
}
