<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PimsCategory extends Model
{
    use HasFactory;

    protected $table = 'csrw_pims_categories';

    protected $fillable = [
        'id',
        'catID',
        'categoryname',
        'status',
    ];
}
