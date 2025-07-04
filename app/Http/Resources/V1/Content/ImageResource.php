<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Content;

use App\Http\Resources\V1\BaseResource;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Request;

class ImageResource extends BaseResource
{
    /**
     * Chuyển đổi ảnh thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin ảnh
     */
    public function toArray(Request $request): array
    {
        $imageUrls = is_string($this->resource->image_url) 
            ? json_decode($this->resource->image_url, true) 
            : $this->resource->image_url;
            
        if (!is_array($imageUrls)) {
            $imageUrls = [$imageUrls];
        }
        
        // Format URLs to full URLs
        $formattedUrls = array_map(fn($url) => $this->formatUrl($url), $imageUrls);
        
        return [
            'id' => $this->resource->id,
            'image_url' => $formattedUrls,
            'prompt' => $this->resource->prompt,
            'title' => $this->resource->title ?? '',
            'sum_like' => $this->resource->sum_like ?? 0,
            'sum_comment' => $this->resource->sum_comment ?? 0,
            'privacy_status' => $this->resource->privacy_status ?? 'public',
            'is_public' => ($this->resource->privacy_status ?? 'public') === 'public',
            'created_at' => $this->formatTimestamp($this->resource->created_at),
            'updated_at' => $this->formatTimestamp($this->resource->updated_at),
            'created_at_formatted' => $this->resource->created_at?->format('d/m/Y H:i'),
            'user' => $this->whenLoaded('user', fn() => new UserResource($this->resource->user)),
        ];
    }
} 