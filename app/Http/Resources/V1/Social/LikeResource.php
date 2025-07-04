<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Social;

use App\Http\Resources\V1\BaseResource;
use Illuminate\Http\Request;

class LikeResource extends BaseResource
{
    /**
     * Chuyển đổi like thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin like
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->resource->user_id,
            'user_name' => $this->resource->user->name,
            'user_avatar' => $this->formatUrl($this->resource->user->avatar_url),
            'liked_at' => $this->formatTimestamp($this->resource->created_at),
            'liked_at_formatted' => $this->resource->created_at?->diffForHumans(),
        ];
    }
} 