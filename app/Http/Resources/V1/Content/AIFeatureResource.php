<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Content;

use App\Http\Resources\V1\BaseResource;
use Illuminate\Http\Request;

class AIFeatureResource extends BaseResource
{
    /**
     * Chuyển đổi AI Feature thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin AI Feature
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'description' => $this->resource->description,
            'category' => $this->resource->category,
            'thumbnail_url' => $this->formatUrl($this->resource->thumbnail_url),
            'status_feature' => $this->resource->status_feature,
            'is_active' => $this->resource->status_feature === 'active',
            'sum_img' => $this->resource->sum_img ?? 0,
            'input_requirements' => $this->resource->input_requirements,
            'created_at' => $this->formatTimestamp($this->resource->created_at),
            'updated_at' => $this->formatTimestamp($this->resource->updated_at),
            'created_at_formatted' => $this->resource->created_at?->format('d/m/Y H:i'),
            'updated_at_formatted' => $this->resource->updated_at?->format('d/m/Y H:i'),
        ];
    }
} 