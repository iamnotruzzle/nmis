<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PimsSupplier extends Model
{
    use HasFactory;

    protected $table = 'csrw_suppliers';

    protected $fillable = [
        'supplierID',
        'suppname',
        'status',
    ];
}
