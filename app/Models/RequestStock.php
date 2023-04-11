<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStock extends Model
{
    use HasFactory;

    protected $table = 'csrw_request_stocks';

    protected $fillable = [
        'id',
        'cl2comb',
        'requested_qty',
        'approved_qty',
        'status',
        'requested_by',
        'requested_at', // ward location
        'approved_by',
    ];
}
