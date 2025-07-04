<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Common;

use App\Http\Resources\V1\BaseResource;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginationResource extends BaseResource
{
    protected LengthAwarePaginator $paginator;
    protected array $data;

    /**
     * Tạo instance resource mới
     * 
     * @param LengthAwarePaginator $paginator
     * @param array $data
     */
    public function __construct(LengthAwarePaginator $paginator, array $data)
    {
        $this->paginator = $paginator;
        $this->data = $data;
    }

    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->data,
            'pagination' => [
                'current_page' => $this->paginator->currentPage(),
                'from' => $this->paginator->firstItem(),
                'last_page' => $this->paginator->lastPage(),
                'per_page' => $this->paginator->perPage(),
                'to' => $this->paginator->lastItem(),
                'total' => $this->paginator->total(),
                'has_more_pages' => $this->paginator->hasMorePages(),
                'on_first_page' => $this->paginator->onFirstPage(),
            ],
            'links' => [
                'first' => $this->paginator->url(1),
                'last' => $this->paginator->url($this->paginator->lastPage()),
                'prev' => $this->paginator->previousPageUrl(),
                'next' => $this->paginator->nextPageUrl(),
            ],
        ];
    }

    /**
     * Tạo pagination response từ paginator và resource collection
     */
    public static function create(LengthAwarePaginator $paginator, string $resourceClass): self
    {
        $data = $resourceClass::collection($paginator->items());
        return new static($paginator, $data->toArray(request()));
    }
} 