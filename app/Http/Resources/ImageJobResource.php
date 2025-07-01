<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ImageJobResource extends JsonResource
{
    /**
     * Chuyển đổi Image Job thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin Image Job
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'feature_id' => $this->feature_id,
            'prompt' => $this->prompt,
            'width' => $this->width,
            'height' => $this->height,
            'seed' => $this->seed,
            'style' => $this->style,
            'status' => $this->status,
            'progress' => $this->progress ?? 0,
            'comfy_prompt_id' => $this->comfy_prompt_id,
            'error_message' => $this->when($this->status === 'failed', $this->error_message),
            'main_image' => $this->main_image,
            'secondary_image' => $this->secondary_image,
            'result_image' => $this->result_image,
            'result_image_url' => $this->result_image_url,
            'is_completed' => $this->status === 'completed',
            'is_failed' => $this->status === 'failed',
            'is_processing' => in_array($this->status, ['pending', 'processing']),
            'can_retry' => $this->status === 'failed',
            'can_cancel' => in_array($this->status, ['pending', 'processing']),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),
            'processing_time' => $this->when($this->status === 'completed', function() {
                return $this->created_at?->diffInMinutes($this->updated_at) . ' phút';
            }),
            
            // Thông tin User (khi cần)
            'user' => $this->whenLoaded('user', function() {
                return new UserResource($this->user);
            }),
            
            // Thông tin Feature (khi cần)
            'feature' => $this->whenLoaded('feature', function() {
                return new AIFeatureResource($this->feature);
            }),
        ];
    }
} 