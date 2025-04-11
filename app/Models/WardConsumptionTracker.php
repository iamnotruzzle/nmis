<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WardConsumptionTracker extends Model
{
    use HasFactory;

    protected $table = 'csrw_ward_consumption_tracker';

    protected $fillable = [
        'ward_stock_id',
        'item_conversion_id',
        'ris_no',
        'cl2comb',
        'uomcode',
        'non_specific_charge',
        'surgery',
        'obgyne',
        'ortho',
        'pedia',
        'optha',
        'ent',
        'initial_qty',
        'beg_bal_qty',
        'end_bal_qty',
        'return_to_csr_qty',
        'transfer_qty',
        'beg_bal_date',
        'end_bal_date',
        'item_from',
        'location',
        'price_id'
    ];
}
