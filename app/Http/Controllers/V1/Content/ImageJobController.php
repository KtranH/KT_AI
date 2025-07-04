<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Content;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Http\Controllers\Constants\ErrorMessages;
use App\Http\Controllers\Constants\SuccessMessages;
use App\Services\Business\ImageJobService;
use App\Http\Requests\Image\JobImageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ImageJobController extends BaseV1Controller
{
    protected ImageJobService $imageJobService;
    
    public function __construct(ImageJobService $imageJobService)
    {
        $this->imageJobService = $imageJobService;
    }
    
    /**
     * Tạo mới một job tạo ảnh AI
     * 
     * @param JobImageRequest $request
     * @return JsonResponse
     */
    public function create(JobImageRequest $request): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->imageJobService->createImageJob($request->validated(), Auth::user()),
            SuccessMessages::IMAGE_JOB_CREATE_SUCCESS,
            ErrorMessages::IMAGE_JOB_CREATE_ERROR
        );
    }
    
    /**
     * Lấy danh sách jobs đang hoạt động của user
     * 
     * @return JsonResponse
     */
    public function getActiveJobs(): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->imageJobService->getActiveJobs(Auth::user()),
            null,
            ErrorMessages::IMAGE_JOB_LOAD_ERROR
        );
    }
    
    /**
     * Lấy danh sách jobs đã hoàn thành của user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompletedJobs(Request $request): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->imageJobService->getCompletedJobs($request),
            null,
            ErrorMessages::IMAGE_JOB_LOAD_ERROR
        );
    }

    /**
     * Lấy danh sách jobs thất bại của user
     * 
     * @return JsonResponse
     */
    public function getFailedJobs(): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->imageJobService->getFailedJobs(Auth::user()),
            null,
            ErrorMessages::IMAGE_JOB_LOAD_ERROR
        );
    }
    
    /**
     * Kiểm tra trạng thái của một job cụ thể
     * 
     * @param int $jobId
     * @return JsonResponse
     */
    public function checkJobStatus(int $jobId): JsonResponse
    {
        if (!$this->isValidId($jobId)) {
            return $this->errorResponseV1(ErrorMessages::INVALID_ID, 400);
        }

        return $this->executeServiceMethodV1(
            fn() => $this->imageJobService->checkJobStatus($jobId, Auth::user()),
            null,
            ErrorMessages::IMAGE_JOB_LOAD_ERROR
        );
    }
    
    /**
     * Hủy một job
     * 
     * @param int $jobId
     * @return JsonResponse
     */
    public function cancelJob(int $jobId): JsonResponse
    {
        if (!$this->isValidId($jobId)) {
            return $this->errorResponseV1(ErrorMessages::INVALID_ID, 400);
        }

        return $this->executeServiceMethodV1(
            fn() => $this->imageJobService->cancelJob($jobId, Auth::user()),
            SuccessMessages::IMAGE_JOB_CANCEL_SUCCESS,
            ErrorMessages::IMAGE_JOB_CANCEL_ERROR
        );
    }
        
    /**
     * Thử lại một job thất bại
     * 
     * @param int $jobId
     * @return JsonResponse
     */
    public function retryJob(int $jobId): JsonResponse
    {
        if (!$this->isValidId($jobId)) {
            return $this->errorResponseV1(ErrorMessages::INVALID_ID, 400);
        }

        return $this->executeServiceMethodV1(
            fn() => $this->imageJobService->retryJob($jobId, Auth::user()),
            SuccessMessages::IMAGE_JOB_RETRY_SUCCESS,
            ErrorMessages::IMAGE_JOB_RETRY_ERROR
        );
    }
} 