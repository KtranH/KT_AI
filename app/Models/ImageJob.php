<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'feature_id',
        'prompt',
        'width',
        'height',
        'seed',
        'style',
        'main_image',
        'secondary_image',
        'result_image',
        'status', // 'pending', 'processing', 'completed', 'failed'
        'comfy_prompt_id',
        'error_message',
        'progress', // thêm cột progress để lưu tiến độ (%)
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'seed' => 'integer',
        'feature_id' => 'integer',
        'user_id' => 'integer',
        'progress' => 'integer',
    ];

    /**
     * Lấy người dùng sở hữu tiến trình
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lấy thông tin feature của tiến trình
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(AIFeature::class, 'feature_id');
    }

    /**
     * Kiểm tra xem người dùng có vượt quá giới hạn tiến trình không
     */
    public static function countActiveJobsByUser(int $userId): int
    {
        return self::where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->count();
    }

    /**
     * Lấy các tiến trình đang hoạt động của người dùng
     */
    public static function getActiveJobsByUser(int $userId)
    {
        return self::where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Lấy các tiến trình đã hoàn thành của người dùng
     */
    public static function getCompletedJobsByUser(int $userId)
    {
        return self::where('user_id', $userId)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
    }
} 