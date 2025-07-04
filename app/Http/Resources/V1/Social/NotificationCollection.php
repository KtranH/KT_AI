<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Social;

use App\Http\Resources\V1\BaseCollection;

class NotificationCollection extends BaseCollection
{
    protected int $unreadCount;

    public function __construct($resource, int $unreadCount = 0)
    {
        parent::__construct($resource);
        $this->unreadCount = $unreadCount;
    }

    /**
     * Transform the resource collection into an array.
     */
    public function toArray($request): array
    {
        return [
            'notifications' => NotificationResource::collection($this->collection),
            'pagination' => [
                'total' => $this->total(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'has_more_pages' => $this->hasMorePages(),
            ],
            'unread_count' => $this->unreadCount,
        ];
    }
} 