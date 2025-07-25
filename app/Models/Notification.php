<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        "id",
        "type",
        "notifiable_type",
        "notifiable_id",
        "data",
        "read_at",
        "created_at",
        "updated_at"
    ];

    protected $casts = [
        "id" => "integer",
        "type" => "string",
        "notifiable_type" => "string",
        "notifiable_id" => "integer",
        "data" => "array",
        "read_at" => "datetime",
        "created_at" => "datetime",
        "updated_at" => "datetime"
    ];

    /**
     * Lấy đối tượng được thông báo
     * return morphTo Đối tượng được thông báo
     */
    public function notifiable()
    {
        return $this->morphTo();
    }
} 