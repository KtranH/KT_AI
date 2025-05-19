<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\CheckImageJobStatus;
use App\Models\ImageJob;
use App\Services\ComfyUIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ImageJobController extends Controller
{
    protected $comfyuiService;
    
    public function __construct(ComfyUIService $comfyuiService)
    {
        $this->comfyuiService = $comfyuiService;
    }
    
    /**
     * Tạo mới một tiến trình tạo ảnh
     */
    public function create(Request $request)
    {
        try {
            // Validate dữ liệu
            $validator = Validator::make($request->all(), [
                'prompt' => 'required|string|max:1000',
                'width' => 'required|integer|min:64|max:2048',
                'height' => 'required|integer|min:64|max:2048',
                'seed' => 'required|numeric',
                'style' => 'nullable|string',
                'feature_id' => 'required|exists:ai_features,id',
                'main_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'secondary_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);
                        
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $user = Auth::user();
            
            // Kiểm tra số lượng tiến trình hiện tại
            $activeJobsCount = ImageJob::countActiveJobsByUser($user->id);
            if ($activeJobsCount >= 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã đạt đến giới hạn 5 tiến trình đang hoạt động. Vui lòng đợi cho đến khi một số tiến trình hoàn thành.'
                ], 429);
            }
            
            // Xử lý tải lên ảnh (nếu có)
            $mainImagePath = null;
            $secondaryImagePath = null;
            
            if ($request->hasFile('main_image')) {
                $mainImagePath = $request->file('main_image')->store('images/inputs');
            }
            
            if ($request->hasFile('secondary_image')) {
                $secondaryImagePath = $request->file('secondary_image')->store('images/inputs');
            }
            
            // Tạo tiến trình
            $imageJob = ImageJob::create([
                'user_id' => $user->id,
                'feature_id' => $request->feature_id,
                'prompt' => $request->prompt,
                'width' => $request->width,
                'height' => $request->height,
                'seed' => $request->seed,
                'style' => $request->style,
                'main_image' => $mainImagePath,
                'secondary_image' => $secondaryImagePath,
                'status' => 'pending',
                'progress' => 0,
            ]);
            
            // Gửi yêu cầu đến ComfyUI
            $promptId = $this->comfyuiService->createImage($imageJob);
            
            if (!$promptId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi khi tạo ảnh với ComfyUI: ' . ($imageJob->error_message ?? 'Lỗi không xác định')
                ], 500);
            }
            
            // Đưa vào queue để kiểm tra tiến trình
            dispatch(new CheckImageJobStatus($imageJob->id, $promptId))
                ->onQueue('image-processing')
                ->delay(now()->addSeconds(15)); // Cho ComfyUI thêm thời gian để bắt đầu xử lý
            
            return response()->json([
                'success' => true,
                'message' => 'Tiến trình tạo ảnh đã được khởi tạo',
                'job_id' => $imageJob->id,
                'status' => $imageJob->status,
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
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
            $user = Auth::user();
            
            // Lấy danh sách các tiến trình đang hoạt động
            $activeJobs = ImageJob::getActiveJobsByUser($user->id);
            
            // Accessor result_image_url sẽ tự động được áp dụng cho mỗi job
            
            return response()->json([
                'success' => true,
                'active_jobs' => $activeJobs,
                'count' => count($activeJobs),
            ]);
            
        } catch (\Exception $e) {
            Log::error("Lỗi khi lấy danh sách tiến trình: " . $e->getMessage());
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
            $user = Auth::user();
            
            // Lấy danh sách các tiến trình đã hoàn thành
            $completedJobs = ImageJob::getCompletedJobsByUser($user->id);
            
            // Accessor result_image_url sẽ tự động được áp dụng cho mỗi job
            
            return response()->json([
                'success' => true,
                'completed_jobs' => $completedJobs,
                'count' => count($completedJobs),
            ]);
            
        } catch (\Exception $e) {
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
            $user = Auth::user();
            
            $job = ImageJob::where('id', $jobId)
                ->where('user_id', $user->id)
                ->first();
                
            if (!$job) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy tiến trình'
                ], 404);
            }
            
            // Nếu tiến trình đang xử lý hoặc đang chờ và có ID prompt, tạo queue job để kiểm tra
            if (($job->status === 'processing' || $job->status === 'pending') && $job->comfy_prompt_id) {
                // Tạo queue job để kiểm tra tiến trình, nhưng ưu tiên thấp và delay 5 giây
                // để tránh trường hợp người dùng spam API này
                dispatch(new CheckImageJobStatus($job->id, $job->comfy_prompt_id))
                    ->onQueue('image-processing-low')
                    ->delay(now()->addSeconds(5));
                
                Log::info("Đã thêm job {$job->id} vào queue để kiểm tra trạng thái");
            }
            
            // Accessor result_image_url sẽ tự động được áp dụng 
            
            return response()->json([
                'success' => true,
                'job' => $job
            ]);
            
        } catch (\Exception $e) {
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
            $user = Auth::user();
            
            $job = ImageJob::where('id', $jobId)
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing'])
                ->firstOrFail();
            
            // Cập nhật trạng thái
            $job->status = 'failed';
            $job->error_message = 'Tiến trình đã bị hủy bởi người dùng';
            $job->save();
            
            // Xóa các ảnh đã tải lên (nếu có)
            if ($job->main_image) {
                Storage::delete($job->main_image);
            }
            
            if ($job->secondary_image) {
                Storage::delete($job->secondary_image);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Tiến trình đã bị hủy'
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tiến trình hoặc tiến trình không thể hủy'
            ], 404);
        } catch (\Exception $e) {
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
            $user = Auth::user();
            
            // Lấy danh sách các tiến trình thất bại
            $failedJobs = ImageJob::where('user_id', $user->id)
                ->where('status', 'failed')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Accessor result_image_url sẽ tự động được áp dụng cho mỗi job
            
            return response()->json([
                'success' => true,
                'failed_jobs' => $failedJobs,
                'count' => count($failedJobs),
            ]);
            
        } catch (\Exception $e) {
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
            $user = Auth::user();
            
            $job = ImageJob::where('id', $jobId)
                ->where('user_id', $user->id)
                ->where('status', 'failed')
                ->firstOrFail();
            
            // Kiểm tra số lượng tiến trình hiện tại
            $activeJobsCount = ImageJob::countActiveJobsByUser($user->id);
            if ($activeJobsCount >= 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã đạt đến giới hạn 5 tiến trình đang hoạt động. Vui lòng đợi cho đến khi một số tiến trình hoàn thành.'
                ], 429);
            }
            
            // Cập nhật trạng thái
            $job->status = 'pending';
            $job->progress = 0;
            $job->error_message = null;
            $job->result_image = null;
            $job->save();
            
            // Gửi yêu cầu đến ComfyUI
            $promptId = $this->comfyuiService->createImage($job);
            
            if (!$promptId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi khi tạo ảnh với ComfyUI: ' . ($job->error_message ?? 'Lỗi không xác định')
                ], 500);
            }
            
            // Đưa vào queue để kiểm tra tiến trình
            dispatch(new CheckImageJobStatus($job->id, $promptId))
                ->onQueue('image-processing')
                ->delay(now()->addSeconds(15));
            
            return response()->json([
                'success' => true,
                'message' => 'Tiến trình đã được khởi động lại',
                'job_id' => $job->id,
                'status' => $job->status,
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tiến trình thất bại'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
} 