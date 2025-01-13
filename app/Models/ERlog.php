<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ERlog extends Model
{
    use HasFactory;

    protected $table = 'herlog';
    protected $primaryKey = 'enccode';
    public $incrementing = false;

    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'enccode',
        'hpercode',
        'erdate',
        'licno',
        'erstat',
        'tscode'
    ];
}
