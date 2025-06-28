<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;
    public function __construct(NotificationService $notificationService) 
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Lấy danh sách thông báo
     * 
     * @param Request $request
     * @return ResourceCollection
     */
    public function index(Request $request): ResourceCollection
    {
        return $this->notificationService->index($request);
    }
    /**
     * Đánh dấu thông báo đã đọc
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function markAsRead(string $id): JsonResponse
    {
        return $this->notificationService->markAsRead($id);
    }
    /**
     * Đánh dấu tất cả thông báo đã đọc
     * 
     * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        return $this->notificationService->markAllAsRead();
    }
    /**
     * Xóa một thông báo
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->notificationService->destroy($id);
    }
}