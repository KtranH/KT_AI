<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\NotificationResource;

class NotificationCollection extends ResourceCollection
{
    protected int $unreadCount;

    public function __construct($resource, int $unreadCount)
    {
        parent::__construct($resource);
        $this->unreadCount = $unreadCount;
    }

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
