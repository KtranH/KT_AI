<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecoveryCode extends Model
{
    use HasFactory;

    /**
     * Chỉ định tên bảng rõ ràng để tránh lỗi Laravel tự động chuyển đổi
     */
    protected $table = 'recovery_codes';

    protected $fillable = [
        'user_id',
        'code',
        'used',
    ];

    protected $casts = [
        'used' => 'boolean',
    ];

    protected $hidden = [
    ];

    /**
     * Relationship với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
