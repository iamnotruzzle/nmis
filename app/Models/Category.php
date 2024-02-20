<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // SECONDARY CATEGORY
    use HasFactory;

    protected $table = 'hclass1';
    protected $primaryKey = 'cl1comb';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'cl1comb',
        'ptcode',
        'cl1code',
        'cl1desc',
        'cl1stat',
        'cl1lock',
        'cl1upsw',
        'cl1dtmd',
        'compense',
        'catID',
    ];
}
