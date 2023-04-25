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
}
