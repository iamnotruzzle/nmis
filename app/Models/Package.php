<?php

namespace App\Models;

use BaconQrCode\Renderer\RendererStyle\Fill;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $table = 'csrw_packages';

    protected $fillable = [
        'description',
        'status',
        'created_by',
        'updated_by',
    ];
}
