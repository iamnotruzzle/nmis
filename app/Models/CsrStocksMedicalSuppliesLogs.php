<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsrStocksMedicalSuppliesLogs extends Model
{
    use HasFactory;
    protected $table = 'csrw_csr_stocks_med_supp_logs';

    protected $fillable = [
        'id',
        'stock_id',
        'ris_no',
        'cl2comb',
        'uomcode',
        'suppcode',
        'brand',
        'chrgcode',
        'prev_qty',
        'new_qty',
        'manufactured_date',
        'delivered_date',
        'expiration_date',
        'action',
        'remarks',
        'entry_by',
    ];
}
