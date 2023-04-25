<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionLog extends Model
{
    use HasFactory;

    protected $table = 'hadmlog';
    protected $primaryKey = 'enccode';
    public $incrementing = false;

    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'enccode',
        'hpercode',
        'admdate',
        'licno',
        'admstat',
    ];
}
