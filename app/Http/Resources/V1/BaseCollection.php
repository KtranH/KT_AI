<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BaseCollection extends ResourceCollection
{
    /**
     * Thêm version header và meta data vào response
     *
     * @param Request $request
     * @return array
     */
    public function with(Request $request): array
    {
        $response = [
            'meta' => [
                'version' => 'v1',
                'timestamp' => now()->toISOString(),
            ]
        ];

        // Thêm pagination meta nếu có
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            $response['meta']['pagination'] = [
                'current_page' => $this->resource->currentPage(),
                'from' => $this->resource->firstItem(),
                'last_page' => $this->resource->lastPage(),
                'per_page' => $this->resource->perPage(),
                'to' => $this->resource->lastItem(),
                'total' => $this->resource->total(),
                'has_more_pages' => $this->resource->hasMorePages(),
            ];

            $response['links'] = [
                'first' => $this->resource->url(1),
                'last' => $this->resource->url($this->resource->lastPage()),
                'prev' => $this->resource->previousPageUrl(),
                'next' => $this->resource->nextPageUrl(),
            ];
        }

        return $response;
    }

    /**
     * Customize response wrapper
     *
     * @param Request $request
     * @param \Illuminate\Http\Response $response
     * @return void
     */
    public function withResponse(Request $request, $response): void
    {
        $response->header('X-API-Version', 'v1');
    }

    /**
     * Transform collection data với custom logic
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
} 