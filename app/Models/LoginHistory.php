<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    protected $table = 'csrw_login_history';

    protected $fillable = [
        'id',
        'employeeid',
        'wardcode',
        'login_token'
    ];

    public function userDetail()
    {
        return $this->belongsTo(UserDetail::class); //use user detail class
    }

    public function locationName()
    {
        return $this->hasOne(Location::class, 'wardcode', 'wardcode');
    }
}
