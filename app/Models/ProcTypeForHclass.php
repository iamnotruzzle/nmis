<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcTypeForHclass extends Model
{
    use HasFactory;

    protected $table = 'hproctyp';
    protected $primaryKey = 'ptcode';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ptcode',
        'ptdesc',
    ];
}
