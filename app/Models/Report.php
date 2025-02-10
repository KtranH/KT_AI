<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'admin_id',
        'image_id',
        'status_report'
    ];

    protected $casts = [
        'status_report' => 'string'
    ];

    // Relationship với Admin (nhiều-1)
    public function admin()
    {
        return $this->belongsTo(AdminUser::class, 'admin_id');
    }

    // Relationship với Image (nhiều-1)
    public function image()
    {
        return $this->belongsTo(Image::class);
    }
} 