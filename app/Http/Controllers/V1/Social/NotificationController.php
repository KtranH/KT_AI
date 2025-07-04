<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Social;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Http\Controllers\Constants\ErrorMessages;
use App\Http\Controllers\Constants\SuccessMessages;
use App\Services\Business\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends BaseV1Controller
{
    protected NotificationService $notificationService;
    
    public function __construct(NotificationService $notificationService) 
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Lấy danh sách notifications
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->notificationService->index($request),
            null,
            ErrorMessages::NOTIFICATION_LOAD_ERROR
        );
    }

    /**
     * Đánh dấu notification đã đọc
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function markAsRead(string $id): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->notificationService->markAsRead($id),
            null,
            ErrorMessages::NOTIFICATION_MARK_AS_READ_ERROR
        );
    }

    /**
     * Đánh dấu tất cả notifications đã đọc
     * 
     * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->notificationService->markAllAsRead(),
            null,
            ErrorMessages::NOTIFICATION_MARK_ALL_AS_READ_ERROR
        );
    }

    /**
     * Xóa một notification
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->notificationService->destroy($id),
            null,
            ErrorMessages::NOTIFICATION_DELETE_ERROR
        );
    }
} 