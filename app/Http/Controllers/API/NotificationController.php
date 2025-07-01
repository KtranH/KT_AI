<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Http\Controllers\ErrorMessages;
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
    public function index(Request $request): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->notificationService->index($request),
            null,
            ErrorMessages::NOTIFICATION_LOAD_ERROR
        );
    }
    /**
     * Đánh dấu thông báo đã đọc
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function markAsRead(string $id): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->notificationService->markAsRead($id),
            null,
            ErrorMessages::NOTIFICATION_MARK_AS_READ_ERROR
        );
    }
    /**
     * Đánh dấu tất cả thông báo đã đọc
     * 
     * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->notificationService->markAllAsRead(),
            null,
            ErrorMessages::NOTIFICATION_MARK_ALL_AS_READ_ERROR
        );
    }
    /**
     * Xóa một thông báo
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->notificationService->destroy($id),
            null,
            ErrorMessages::NOTIFICATION_DELETE_ERROR
        );
    }
}