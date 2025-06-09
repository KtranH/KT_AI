<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ImageJobService;
use App\Http\Requests\Image\JobImageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImageJobController extends Controller
{
    protected ImageJobService $imageJobService;
    
    public function __construct(ImageJobService $imageJobService)
    {
        $this->imageJobService = $imageJobService;
    }
    
    /**
     * Tạo mới một tiến trình tạo ảnh
     */
    public function create(JobImageRequest $request)
    {
        try {
            $validated = $request->validated(); // chỉ lấy dữ liệu đã qua validate
            $result = $this->imageJobService->createImageJob($validated, Auth::user());
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error("Lỗi khi tạo tiến trình tạo ảnh: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lấy danh sách các tiến trình đang hoạt động của người dùng
     */
    public function getActiveJobs(Request $request)
    {
        try {
            $result = $this->imageJobService->getActiveJobs(Auth::user());
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error("Lỗi khi lấy danh sách tiến trình đang hoạt động: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lấy danh sách các tiến trình đã hoàn thành của người dùng
     */
    public function getCompletedJobs(Request $request)
    {
        try {
            $result = $this->imageJobService->getCompletedJobs(Auth::user());
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error("Lỗi khi lấy danh sách tiến trình đã hoàn thành: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Kiểm tra trạng thái của một tiến trình cụ thể
     */
    public function checkJobStatus(Request $request, $jobId)
    {
        try {
            $result = $this->imageJobService->checkJobStatus((int) $jobId, Auth::user());
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error("Lỗi khi kiểm tra trạng thái tiến trình {$jobId}: " . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'Không tìm thấy')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Hủy một tiến trình
     */
    public function cancelJob(Request $request, $jobId)
    {
        try {
            $result = $this->imageJobService->cancelJob((int) $jobId, Auth::user());
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error("Lỗi khi hủy tiến trình {$jobId}: " . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'Không tìm thấy')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Lấy danh sách các tiến trình thất bại của người dùng
     */
    public function getFailedJobs(Request $request)
    {
        try {
            $result = $this->imageJobService->getFailedJobs(Auth::user());
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error("Lỗi khi lấy danh sách tiến trình thất bại: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Thử lại một tiến trình thất bại
     */
    public function retryJob(Request $request, $jobId)
    {
        try {
            $result = $this->imageJobService->retryJob((int) $jobId, Auth::user());
            
            return response()->json($result);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Lỗi khi thử lại tiến trình {$jobId}: " . $e->getMessage());
            
            if (str_contains($e->getMessage(), 'Không tìm thấy')) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 404);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
} 