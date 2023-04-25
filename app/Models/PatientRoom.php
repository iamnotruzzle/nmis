<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientRoom extends Model
{
    use HasFactory;

    protected $table = 'hpatroom';

    protected $fillable = [
        'enccode',
        'hpercode',
        'wardcode',
        'rmintkey',
        'bdintkey',
        'rmvcode',
        'patrmstat'
    ];

    public function patient()
    {
        return $this->hasMany(Patient::class, 'hpercode', 'hpercode');
    }

    public function ward()
    {
        return $this->hasMany(Location::class, 'wardcode', 'wardcode');
    }

    public function room()
    {
        return $this->hasMany(RoomType::class, 'rmintkey', 'rmintkey');
    }

    public function bed()
    {
        return $this->hasMany(BedType::class, 'bdintkey', 'bdintkey');
    }
}
