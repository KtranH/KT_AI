<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPermission extends Model
{
    protected $table = 'admin_permissions';

    protected $fillable = [
        'name',
        'slug',
        'description'
    ];

    // Relationship với AdminRolePermission (1-nhiều)
    public function rolePermissions()
    {
        return $this->hasMany(AdminRolePermission::class, 'permission_id');
    }
} 