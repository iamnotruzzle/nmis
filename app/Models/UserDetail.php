<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    use HasFactory;

    protected $table = 'hpersonal';
    protected $primaryKey = 'employeeid';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'employeeid',
        'lastname',
        'firstname',
        'middlename',
        'empsuffix',
        'empprefix',
        'empstat',
        'updsw',
        'datemod',
        'emplock',
        'empstat',
        'postitle',
        'paddr',
        'tin',
        'deptcode',
        'entryby',
        'provbdate',
        'provsex',
        'plantstat',
        'contactno',
        'opdstat',
    ];

    public function user_account()
    {
        return $this->hasOne(User::class, 'employeeid', 'employeeid');
    }

    public function endorsementsGiven()
    {
        return $this->hasMany(WaEndorsement::class, 'from_user', 'employeeid');
    }

    public function endorsementsReceived()
    {
        return $this->hasMany(WaEndorsement::class, 'to_user', 'employeeid');
    }
}
