<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionLog extends Model
{
    use HasFactory;

    protected $table = 'hadmlog';
    protected $primaryKey = 'enccode';
    public $incrementing = false;

    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'enccode',
        'hpercode',
        'admdate',
        'licno',
        'admstat',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'hpercode', 'hpercode');
    }

    // physician
    // this was done because hadmlog has many licno #s as foreign keys
    public function physician()
    {
        return $this->hasOne(PhysicianLicense::class, 'licno', 'licnof')->with(['userDetail:employeeid,lastname,firstname,middlename']);
    }
    public function physician2()
    {
        return $this->hasOne(PhysicianLicense::class, 'licno', 'licno2')->with(['userDetail:employeeid,lastname,firstname,middlename']);
    }
    public function physician3()
    {
        return $this->hasOne(PhysicianLicense::class, 'licno', 'licno3')->with(['userDetail:employeeid,lastname,firstname,middlename']);
    }
    public function physician4()
    {
        return $this->hasOne(PhysicianLicense::class, 'licno', 'licno4')->with(['userDetail:employeeid,lastname,firstname,middlename']);
    }
    // end physician

    public function doctorOrder()
    {
        return $this->hasMany(DoctorsOrder::class, 'enccode', 'enccode')
            ->where('orcode', 'DIETT')
            ->where('dostat', 'A');
    }

    public function dischargeOrder()
    {
        return $this->hasMany(DoctorsOrder::class, 'enccode', 'enccode')
            ->where('orcode', 'DISCH')
            ->where('dostat', 'A');
    }


    public function bmi()
    {
        return $this->hasOne(PatientHeightWeight::class, 'enccode', 'enccode')
            ->where('othrstat', 'A')
            ->orderBy('othrdte', 'DESC');
    }

    public function diet()
    {
        return $this->hasMany(DietType::class, 'orcode', 'dietcode');
    }

    public function patientBillStat()
    {
        return $this->hasOne(PatientBillStat::class, 'enccode', 'enccode');
        // ->where('billstat', 02)
        // ->orWhere('billstat', 03);
    }

    public function patientCharge()
    {
        return $this->hasMany(PatientCharge::class, 'enccode', 'enccode')->with(['typeOfCharge', 'item', 'misc', 'patientChargeLogs'])
            ->orderBy('pcchrgdte', 'ASC');
    }
}
