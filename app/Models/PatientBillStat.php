<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientBillStat extends Model
{
    use HasFactory;

    protected $table = 'hactrack';
    protected $primaryKey = 'enccode';
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'enccode',
        'hpercode',
        'encdate',
        'enctime',
        'toecode',
        'wardpatrec',
        'billstat'
    ];
}
