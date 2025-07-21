<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenericVariants extends Model
{
    use HasFactory;

    protected $table = 'csrw_generic_variants';

    protected $fillable = [
        'generic_cl2comb',
        'variant_cl2comb',
    ];
}
