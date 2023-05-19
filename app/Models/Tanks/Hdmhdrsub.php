<?php

namespace App\Models\Tanks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hdmhdrsub extends Model
{
    use HasFactory;

    protected $table = 'hdmhdrsub';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'dmdcomb',
        'dmdctr',
        'dmhdrsub',
        'begbal',
        'baldteasof',
        'stockbal',
        'barcode',
        'entryby',
        'datemod',
        'rpoint',
        'subtic',
        'priority',
        'statusMed',
        'qty_allotment',
        'reorder_level',
    ];
}
