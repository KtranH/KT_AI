<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class PaginateAndRespondResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $resource = $this->resource;

        // Kiểm tra nếu resource là null
        if (is_null($resource)) {
            return [
                'success' => true,
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 0,
                    'total' => 0
                ]
            ];
        }

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

        // Kiểm tra nếu resource là Collection
        if ($resource instanceof Collection) {
            return [
                'success' => true,
                'data' => $resource->all(), // Trả về mảng từ collection
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $resource->count(),
                    'total' => $resource->count()
                ]
            ];
        }

        // Nếu là mảng
        if (is_array($resource)) {
            return [
                'success' => true,
                'data' => $resource,
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => count($resource),
                    'total' => count($resource)
                ]
            ];
        }

        // Trường hợp khác, trả về dữ liệu nguyên bản
        return [
            'success' => true,
            'data' => $resource,
            'pagination' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 1,
                'total' => 1
            ]
        ];
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        return new JsonResponse($this->toArray($request));
    }
}
