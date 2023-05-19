<?php

namespace App\Models\Tanks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hdmhdr extends Model
{
    use HasFactory;

    protected $table = 'hdmhdr';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'dmdcomb',
        'strecode',
        'grpcode',
        'formcode',
        'rtecode',
    ];
}
