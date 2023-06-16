<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardTransferStock extends Model
{
    use HasFactory;

    protected $table = 'csrw_ward_transfer_stock';

    protected $fillable = [
        'id',
        'ward_stock_id',
        'from',
        'to',
        'requested_by',
        'approved_by',
        'quantity',
        'remarks',
        'status', // transferred or received
    ];
}
