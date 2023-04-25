<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $table = 'hroom';
    protected $primaryKey = 'rmintkey';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'rmintkey',
        'wardcode',
        'rmcode',
        'rmname',
        'rmbed',
        'rmvacoc',
        'rmstat',
        'rmlock',
        'datemode',
        'updsw',
        'hrmfloor',
        'nobed',
        'roombaby',
        'rmtype'
    ];

    public function ward()
    {
        return $this->belongsTo(Location::class, 'wardcode', 'wardcode');
    }

    public function bed()
    {
        return $this->hasMany(BedType::class, 'rmintkey', 'rmintkey');
    }
}
