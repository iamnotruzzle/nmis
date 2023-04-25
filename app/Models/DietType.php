<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DietType extends Model
{
    use HasFactory;

    protected $table = 'hdiet';
    protected $primaryKey = 'dietcode';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'dietcode',
        'dietdesc',
        'dietstat',
    ];
}
