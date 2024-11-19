<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CsrStockBalance extends Model
{
    use HasFactory;

    protected $table = "csrw_csr_stock_balance";

    protected $fillable = [
        "cl2comb",
        "beginning_balance",
        "ending_balance",
        "beg_bal_created_at",
        "end_bal_created_at",
        "entry_by",
    ]
}
