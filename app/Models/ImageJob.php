<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
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
        'comfy_prompt_id', // prompt id này có dạng: a9f04379-17218....
        'error_message',
        'progress', // thêm cột progress để lưu tiến độ (%)
        'credits_refunded', // flag để tránh duplicate refund credits
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'feature_id' => 'integer',
        'prompt' => 'string',
        'width' => 'integer',
        'height' => 'integer',
        'seed' => 'integer',
        'style' => 'string',
        'main_image' => 'string',
        'secondary_image' => 'string',
        'result_image' => 'string',
        'status' => 'string',
        'comfy_prompt_id' => 'string',
        'error_message' => 'string',
        'progress' => 'float',
        'credits_refunded' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'result_image_url'
    ];

    /**
     * Accessor cho result_image_url
     * @return string URL của ảnh kết quả
     */
    public function getResultImageUrlAttribute()
    {
        if (!$this->result_image) {
            return null;
        }
        
        // Nếu đã là URL đầy đủ thì trả về luôn
        if (str_starts_with($this->result_image, 'http')) {
            return $this->result_image;
        }
        
        // Nếu không, chuyển đổi thành URL
        return url(\Illuminate\Support\Facades\Storage::url($this->result_image));
    }

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
} 