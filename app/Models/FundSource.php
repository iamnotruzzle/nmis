<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundSource extends Model
{
    use HasFactory;

    protected $table = 'csrw_fund_source';

    protected $fillable = [
        'id',
        'fsid',
        'fsName',
        'cluster_code'
    ];
}
