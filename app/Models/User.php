<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primary_key = 'id';

    protected $fillable = [
        'id',
        'email',
        'last_login',
        'password',
        'active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',

    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // public function user_profile()
    // {
    //     return $this->hasOne(UserProfile::class, 'user_id');
    // }

    public function saveLogin()
    {
        $this->last_login = now();
        $this->save();
    }

}
