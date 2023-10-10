<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcTypeForHclass extends Model
{
    // MAIN CATEGORY
    use HasFactory;

    protected $table = 'hproctyp'; // list of proc type for hclass tables
    protected $primaryKey = 'ptcode';
    public $incrementing = false;
    // declare primary as string
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ptcode',
        'ptdesc',
    ];

    public function subCategory()
    {
        return $this->hasMany(Category::class, 'ptcode', 'ptcode');
    }
}
