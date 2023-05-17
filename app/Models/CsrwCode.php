<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsrwCode extends Model
{
    use HasFactory;

    protected $table = 'csrw_code';

    protected $fillable =  [
        'id',
        'charge_desc',
        'created_at',
        'updated_at'
    ];
}
