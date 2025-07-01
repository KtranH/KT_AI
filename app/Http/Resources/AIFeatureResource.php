<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class AIFeatureResource extends JsonResource
{
    /**
     * Chuyển đổi AI Feature thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin AI Feature
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'thumbnail_url' => $this->thumbnail_url,
            'status_feature' => $this->status_feature,
            'is_active' => $this->status_feature === 'active',
            'sum_img' => $this->sum_img ?? 0,
            'input_requirements' => $this->input_requirements,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at_formatted' => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }
} 