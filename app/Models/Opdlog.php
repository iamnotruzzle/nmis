<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opdlog extends Model
{
    use HasFactory;

    protected $table = 'hopdlog';
    protected $primaryKey = 'enccode';
    public $incrementing = false;

    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'enccode',
        'hpercode',
        'opddate',
        'licno',
        'opdstat',
        'tscode'
    ];
}
