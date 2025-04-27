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
    public function index(Request $request): ResourceCollection
    {
        return $this->notificationService->index($request);
    }
    public function markAsRead(string $id): JsonResponse
    {
        return $this->notificationService->markAsRead($id);
    }
    public function markAllAsRead(): JsonResponse
    {
        return $this->notificationService->markAllAsRead();
    }
    public function destroy(string $id): JsonResponse
    {
        return $this->notificationService->destroy($id);
    }
}