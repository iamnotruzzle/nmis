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
        // return $this->hasMany(Patient::class, 'hpercode', 'hpercode');
        return $this->hasOne(Patient::class, 'hpercode', 'hpercode')
            ->with('admissionDate:admdate,admstat,casenum,enccode,hpercode,licno,licno3,tacode,tscode');
    }

    public function ward()
    {
        return $this->hasOne(Location::class, 'wardcode', 'wardcode');
    }

    public function room()
    {
        return $this->hasOne(RoomType::class, 'rmintkey', 'rmintkey');
    }

    public function bed()
    {
        return $this->hasOne(BedType::class, 'bdintkey', 'bdintkey');
    }
}
