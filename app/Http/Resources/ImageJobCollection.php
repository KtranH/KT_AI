<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\ImageJobResource;
use Illuminate\Http\Request;

class ImageJobCollection extends ResourceCollection
{
    /**
     * Chuyển đổi collection thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin tiến trình
     */
    public function toArray($request): array
    {
        return [
            'jobs' => ImageJobResource::collection($this->collection),
            'pagination' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'has_more_pages' => $this->hasMorePages(),
            ],
        ];
    }
}
