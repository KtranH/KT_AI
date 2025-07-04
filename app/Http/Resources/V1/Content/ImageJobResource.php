<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Content;

use App\Http\Resources\V1\BaseResource;
use App\Http\Resources\V1\User\UserResource;
use Illuminate\Http\Request;

class ImageJobResource extends BaseResource
{
    /**
     * Chuyển đổi Image Job thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin Image Job
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id,
            'feature_id' => $this->resource->feature_id,
            'prompt' => $this->resource->prompt,
            'width' => $this->resource->width,
            'height' => $this->resource->height,
            'seed' => $this->resource->seed,
            'style' => $this->resource->style,
            'status' => $this->resource->status,
            'progress' => $this->resource->progress ?? 0,
            'comfy_prompt_id' => $this->resource->comfy_prompt_id,
            'error_message' => $this->when($this->resource->status === 'failed', $this->resource->error_message),
            'main_image' => $this->formatUrl($this->resource->main_image),
            'secondary_image' => $this->formatUrl($this->resource->secondary_image),
            'result_image' => $this->formatUrl($this->resource->result_image),
            'result_image_url' => $this->formatUrl($this->resource->result_image_url),
            'status_info' => [
                'is_completed' => $this->resource->status === 'completed',
                'is_failed' => $this->resource->status === 'failed',
                'is_processing' => in_array($this->resource->status, ['pending', 'processing']),
                'can_retry' => $this->resource->status === 'failed',
                'can_cancel' => in_array($this->resource->status, ['pending', 'processing']),
            ],
            'created_at' => $this->formatTimestamp($this->resource->created_at),
            'updated_at' => $this->formatTimestamp($this->resource->updated_at),
            'created_at_formatted' => $this->resource->created_at?->format('d/m/Y H:i'),
            'processing_time' => $this->when($this->resource->status === 'completed', function() {
                return $this->resource->created_at?->diffInMinutes($this->resource->updated_at) . ' phút';
            }),
            
            // Thông tin User (khi cần)
            'user' => $this->whenLoaded('user', fn() => new UserResource($this->resource->user)),
            
            // Thông tin Feature (khi cần)
            'feature' => $this->whenLoaded('feature', fn() => new AIFeatureResource($this->resource->feature)),
        ];
    }
} 