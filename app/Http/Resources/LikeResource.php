<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class LikeResource extends JsonResource
{
    /**
     * Chuyển đổi like thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin like
     */
    public function toArray($request): array
    {
        return [
            'user_id' => $this->user_id,
            'user_name' => $this->user->name,
        ];
    }
}
