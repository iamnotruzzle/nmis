<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $table = 'hperson';
    protected $primarykey = 'hpercode';
    public $incrementing = false;

    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'hpatkey',
        'hpercode',
        'hpatcode',
        'patfirst', // patient first name
        'patmiddle', // patient middle name
        'patlast', // patient last name
        'patsuffix', // patient suffix
        'patbdate', // patient birth date
    ];

    public function admission()
    {
        return $this->hasMany(AdmissionLog::class, 'hpercode', 'hpercode');
    }

    public function admissionDate()
    {
        // return $this->hasMany(AdmissionLog::class, 'hpercode', 'hpercode')->where('disdate', null);
        return $this->hasMany(AdmissionLog::class, 'hpercode', 'hpercode')->with(['doctorOrder', 'physician', 'physician2', 'physician3', 'physician4', 'dischargeOrder', 'bmi:enccode,vsweight,vsheight'])->where('disdate', null);
    }

    public function doctorOrder()
    {
        // return $this->hasMany(DoctorsOrder::class, 'hpercode', 'hpercode')->orderBy('dodate', 'desc');
        return $this->hasMany(DoctorsOrder::class, 'hpercode', 'hpercode')->with(['docOrderType', 'diet:dietcode,dietdesc'])->orderBy('dodate', 'desc');
    }
}
