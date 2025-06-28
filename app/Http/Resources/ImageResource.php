<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ImageResource extends JsonResource
{
    /**
     * Chuyển đổi ảnh thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin ảnh
     */
    public function toArray($request): array
    {
        $imageUrls = is_string($this->image_url) ? json_decode($this->image_url, true) : $this->image_url;
        if (!is_array($imageUrls)) {
            $imageUrls = [$imageUrls];
        }
        
        return [
            'id' => $this->id,
            'image_url' => $imageUrls,
            'prompt' => $this->prompt,
            'sum_like' => $this->sum_like,
            'sum_comment' => $this->sum_comment,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => $this->whenLoaded('user'),
        ];
    }
} 