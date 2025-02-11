<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class AdminRolePermission extends Model
{
    protected $table = 'admin_role_permissions';

    protected $fillable = [
        'role',
        'permission_id'
    ];

    protected $casts = [
        'role' => 'string'
    ];

    // Relationship với AdminUser (nhiều-1)
    public function adminUsers()
    {
        return $this->belongsTo(AdminUser::class, 'role', 'role');
    }

    // Relationship với AdminPermission (nhiều-1)
    public function permission()
    {
        return $this->belongsTo(AdminPermission::class, 'permission_id');
    }
} 