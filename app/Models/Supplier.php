<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'hsupplier';
    protected $primaryKey = 'suppcode';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'suppcode',
        'suppname',
        'suppaddr',
        'supptelno1',
        'supptelno2',
        'suppstat',
    ];
}
