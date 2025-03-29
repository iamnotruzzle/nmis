<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardConsumptionTracker extends Model
{
    use HasFactory;

    protected $table = 'csrw_ward_consumption_tracker';

    protected $fillable = [
        'wards_stocks_id',
        'ris_no',
        'cl2comb',
        'uomcode',
        'received_qty',
        'charged_qty',
        'return_to_csr_qty',
        'transfer_qty',
        'item_from',
        'location',
        'price_id'
    ];
}
