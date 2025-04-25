<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginateAnRespondResource extends JsonResource
{
    public function toArray($request)
    {
        $resource = $this->resource;

        // Kiểm tra nếu resource là LengthAwarePaginator
        if ($resource instanceof LengthAwarePaginator) {
            return [
                'success' => true,
                'data' => $resource->items(), // Dữ liệu chính nếu là paginator
                'pagination' => [
                    'current_page' => $resource->currentPage(),
                    'last_page' => $resource->lastPage(),
                    'per_page' => $resource->perPage(),
                    'total' => $resource->total()
                ]
            ];
        }

        // Nếu không phải paginator (mảng hoặc collection)
        return [
            'success' => true,
            'data' => $resource, // Trả về mảng hoặc collection nguyên vẹn
            'pagination' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => count($resource),
                'total' => count($resource)
            ]
        ];
    }
}
