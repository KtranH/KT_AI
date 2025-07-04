<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Social;

use App\Http\Resources\V1\BaseResource;
use Illuminate\Http\Request;

class NotificationResource extends BaseResource
{
    /**
     * Chuyển đổi notification thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin notification
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'type' => $this->resource->type,
            'data' => $this->resource->data,
            'read_at' => $this->formatTimestamp($this->resource->read_at),
            'is_read' => $this->resource->read_at !== null,
            'created_at' => $this->formatTimestamp($this->resource->created_at),
            'updated_at' => $this->formatTimestamp($this->resource->updated_at),
            'created_at_formatted' => $this->resource->created_at?->diffForHumans(),
        ];
    }
} 