<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhysicianLicense extends Model
{
    use HasFactory;

    protected $table = 'hprovider';
    protected $primaryKey = 'licno';
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'licno',
        'employeeid',
        'empstat'
    ];

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'employeeid');
    }
}
