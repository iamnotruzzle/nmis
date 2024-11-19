<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsrStockbalDateLogs extends Model
{
    use HasFactory;

    protected $table = 'csrw_csr_stock_bal_date_logs';

    protected $fillable = [
        "beg_bal_created_at",
        "end_bal_created_at"
    ];
}
