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
}
