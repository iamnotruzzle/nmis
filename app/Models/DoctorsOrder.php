<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorsOrder extends Model
{
    use HasFactory;

    protected $table = 'hdocord';
    protected $primaryKey = 'docointkey';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'docointkey',
        'enccode',
        'dodate',
        'dotime',
        'licno',
        'ordcon',
        'orcode',
        'hpercode',
        'upicode',
        'dopriority',
        'dodtepost',
        'dotmepost',
        'dostat',
        'ordreas',
        'doctype',
        'orderupd',
        'proccode',
        'dietcode',
        'resultpdf',
    ];

    public function diet()
    {
        return $this->hasMany(DietType::class, 'dietcode', 'dietcode');
    }

    public function admission()
    {
        return $this->hasOne(AdmissionLog::class, 'enccode', 'enccode');
    }

    public function docOrderType()
    {
        return $this->hasOne(DoctorsOrderType::class, 'proccode', 'proccode');
    }
}
