<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sessions extends Model
{
    use HasFactory;

    protected $table = 'sessions';
    public $timestamps = false;

    protected $fillable = [
        'used_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
        'location',
    ];
}
