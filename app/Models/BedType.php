<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BedType extends Model
{
    use HasFactory;


    protected $table = 'hbed';
    protected $primaryKey = 'bdintkey';
    public $incrementing = false;

    // declare primary as string
    protected $keyType = 'string';

    protected $fillable = [
        'bdintkey',
        'wardcode',
        'rmintkey',
        'bdcode',
        'rmaccikey',
        'bddteasof',
        'bdname',
        'bdvacocc',
        'bdstat',
        'bdlock',
        'datemod',
        'updsw',
        'bdvmostan',
        'bdtemp',
        'bdallow',
        'bdactual',
        'bdflag',
        'bdpdteasof',
        'entryby'
    ];
}
