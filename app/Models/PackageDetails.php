<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageDetails extends Model
{
    use HasFactory;

    protected $table = 'csrw_package_details';

    protected $fillable = [
        'package_id',
        'cl2comb',
        'quantity',
    ];
}
