<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationCollection extends ResourceCollection
{
    protected int $unreadCount;

    public function __construct($resource, int $unreadCount)
    {
        parent::__construct($resource);
        $this->unreadCount = $unreadCount;
    }

    /**
     * Chuyển đổi collection thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin notification
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
