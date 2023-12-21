<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use Notifiable;
    protected $guard = 'admin';

    protected $fillable = [
        'name','email', 'password', 'created_at', 'updated_at',
    ];
    protected $hidden = [
        'password', 'remember_token',
    ];
}
