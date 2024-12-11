<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{


    use HasRoles;
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;


    protected $table = 'user_acc';

    protected $primaryKey = 'employeeid'; // Specify custom primary key
    protected $keyType = 'string'; // If your primary key is not an integer
    public $incrementing = false; // If the key is not auto-incrementing

    protected $fillable = [
        'user_name',
        'user_level',
        'user_pass',
        'user_exp',
        'employeeid',
        'designation',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'created_at' => 'date:m-d-Y',
    ];

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'employeeid', 'employeeid');
    }
}
