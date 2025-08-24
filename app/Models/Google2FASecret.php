<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Google2FASecret extends Model
{
    use HasFactory;

    /**
     * Chỉ định tên bảng rõ ràng để tránh lỗi Laravel tự động chuyển đổi
     */
    protected $table = 'google2fa_secrets';

    protected $fillable = [
        'user_id',
        'secret_key',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    protected $hidden = [
        'secret_key',
    ];

    /**
     * Relationship với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
