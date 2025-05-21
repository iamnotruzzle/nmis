<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaEndorsement extends Model
{
    use HasFactory;

    // ward assistant endorsements
    protected $table = 'csrw_wa_endorsements';

    protected $fillable = [
        'from_user',
        'to_user',
        'wardcode',
    ];

    public function ward()
    {
        return $this->hasOne(Location::class, 'wardcode', 'wardcode');
    }

    public function details()
    {
        return $this->hasMany(WaEndorsementDetail::class, 'endorsement_id');
    }

    public function from_user()
    {
        return $this->belongsTo(UserDetail::class, 'from_user', 'employeeid');
    }

    public function to_user()
    {
        return $this->belongsTo(UserDetail::class, 'to_user', 'employeeid');
    }
}
