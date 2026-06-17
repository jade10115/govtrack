<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'user_level_id'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function userLevel()
    {
        return $this->belongsTo(UserLevel::class);
    }

    public function assignedRequests()
    {
        return $this->hasMany(
            ClientRequest::class,
            'assigned_to'
        );
    }
}