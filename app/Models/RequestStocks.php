<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStocks extends Model
{
    use HasFactory;

    protected $table = 'csrw_request_stocks';

    protected $fillable = [
        'id',
        'location',
        'status',
        'requested_by',
        'approved_by',
        'received_date',
    ];

    public function requested_at_details()
    {
        return $this->hasOne(Location::class, 'location', 'wardcode');
    }
}
