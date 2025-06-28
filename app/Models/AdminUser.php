<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    protected $table = 'admin_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'last_login_at',
        'status_admin'
    ];

    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'status_admin' => 'string',
        'role' => 'string'
    ];

    // Relationship với Report (1-nhiều)
    public function reports()
    {
        return $this->hasMany(Report::class, 'admin_id');
    }

    // Relationship với AdminRolePermission (1-nhiều)
    public function rolePermissions()
    {
        return $this->hasMany(AdminRolePermission::class, 'role', 'role');
    }
} 