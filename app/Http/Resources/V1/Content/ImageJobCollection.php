<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Content;

use App\Http\Resources\V1\BaseCollection;

class ImageJobCollection extends BaseCollection
{
    /**
     * Transform the resource collection into an array.
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