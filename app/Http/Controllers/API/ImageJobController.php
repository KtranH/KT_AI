<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ErrorMessages;
use App\Http\Controllers\SuccessMessages;
use App\Services\ImageJobService;
use App\Http\Requests\Image\JobImageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class ImageJobController extends Controller
{
    protected ImageJobService $imageJobService;
    
    public function __construct(ImageJobService $imageJobService)
    {
        $this->imageJobService = $imageJobService;
    }
    
    /**
     * Tạo mới một tiến trình tạo ảnh   
     * 
     * @param JobImageRequest $request
     * @return JsonResponse
     */
    public function create(JobImageRequest $request): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->imageJobService->createImageJob($request->validated(), Auth::user()),
            SuccessMessages::IMAGE_JOB_CREATE_SUCCESS,
            ErrorMessages::IMAGE_JOB_CREATE_ERROR
        );
    }
    
    /**
     * Lấy danh sách các tiến trình đang hoạt động của người dùng
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getActiveJobs(Request $request): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->imageJobService->getActiveJobs(Auth::user()),
            null,
            ErrorMessages::IMAGE_JOB_LOAD_ERROR
        );
    }
    
    /**
     * Lấy danh sách các tiến trình đã hoàn thành của người dùng
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompletedJobs(Request $request): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->imageJobService->getCompletedJobs($request),
            null,
            ErrorMessages::IMAGE_JOB_LOAD_ERROR
        );
    }
    
    /**
     * Kiểm tra trạng thái của một tiến trình cụ thể
     * 
     * @param Request $request
     * @param int $jobId
     * @return JsonResponse
     */
    public function checkJobStatus(Request $request, int $jobId): JsonResponse
    {
        // Validate jobId
        if (!$this->isValidId($jobId)) {
            return $this->errorResponse('ID tiến trình không hợp lệ', 400);
        }

        return $this->executeServiceMethod(
            fn() => $this->imageJobService->checkJobStatus($jobId, Auth::user()),
            null,
            ErrorMessages::IMAGE_JOB_LOAD_ERROR
        );
    }
    
    /**
     * Hủy một tiến trình
     * 
     * @param Request $request
     * @param int $jobId
     * @return JsonResponse
     */
    public function cancelJob(Request $request, int $jobId): JsonResponse
    {
        // Validate jobId
        if (!$this->isValidId($jobId)) {
            return $this->errorResponse('ID tiến trình không hợp lệ', 400);
        }

        return $this->executeServiceMethod(
            fn() => $this->imageJobService->cancelJob($jobId, Auth::user()),
            SuccessMessages::IMAGE_JOB_CANCEL_SUCCESS,
            ErrorMessages::IMAGE_JOB_CANCEL_ERROR
        );
    }
    
    /**
     * Lấy danh sách các tiến trình thất bại của người dùng
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getFailedJobs(Request $request): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->imageJobService->getFailedJobs(Auth::user()),
            null,
            ErrorMessages::IMAGE_JOB_LOAD_ERROR
        );
    }
    
    /**
     * Thử lại một tiến trình thất bại
     * 
     * @param Request $request
     * @param int $jobId
     * @return JsonResponse
     */
    public function retryJob(Request $request, int $jobId): JsonResponse
    {
        // Validate jobId
        if (!$this->isValidId($jobId)) {
            return $this->errorResponse('ID tiến trình không hợp lệ', 400);
        }

        return $this->executeServiceMethod(
            fn() => $this->imageJobService->retryJob($jobId, Auth::user()),
            SuccessMessages::IMAGE_JOB_RETRY_SUCCESS,
            ErrorMessages::IMAGE_JOB_RETRY_ERROR
        );
    }
} 