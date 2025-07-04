<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\User;

use App\Http\Resources\V1\BaseResource;
use Illuminate\Http\Request;

class UserResource extends BaseResource
{
    /**
     * Chuyển đổi user data thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin user
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'email_verified_at' => $this->formatTimestamp($this->resource->email_verified_at),
            'is_verified' => $this->resource->is_verified,
            'avatar_url' => $this->formatUrl($this->resource->avatar_url),
            'cover_image_url' => $this->formatUrl($this->resource->cover_image_url),
            'remaining_credits' => $this->resource->remaining_credits ?? 0,
            'sum_like' => $this->resource->sum_like ?? 0,
            'sum_img' => $this->resource->sum_img ?? 0,
            'status' => [
                'is_active' => $this->resource->is_verified,
                'can_generate' => $this->resource->is_verified && ($this->resource->remaining_credits ?? 0) > 0,
                'member_since' => $this->resource->created_at?->format('d/m/Y'),
            ],
            'created_at' => $this->formatTimestamp($this->resource->created_at),
            'updated_at' => $this->formatTimestamp($this->resource->updated_at),
        ];
    }

    /**
     * Tạo public profile resource (ít thông tin hơn)
     */
    public static function publicProfile($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar_url' => $user->avatar_url ? asset($user->avatar_url) : null,
            'sum_like' => $user->sum_like ?? 0,
            'sum_img' => $user->sum_img ?? 0,
            'member_since' => $user->created_at?->format('d/m/Y'),
            'is_verified' => $user->is_verified,
        ];
    }
} 