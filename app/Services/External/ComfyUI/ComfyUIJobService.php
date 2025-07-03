<?php

namespace App\Services\External\ComfyUI;

use App\Services\BaseService;
use App\Exceptions\BusinessException;
use App\Exceptions\ExternalServiceException;
use App\Models\ImageJob;
use App\Services\External\R2StorageService;
use App\Notifications\ImageGenerationFailedNotification;
use App\Notifications\ImageGeneratedNotification;
use Illuminate\Support\Facades\Log;
use Exception;

class ComfyUIJobService extends BaseService
{
    protected ComfyUIApiService $apiService;
    
    public function __construct(ComfyUIApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    /**
     * Cập nhật tiến trình đã hoàn thành
     */
    public function updateCompletedJob(ImageJob $job, array $historyData, R2StorageService $r2Service): bool
    {
        return $this->executeInTransactionSafely(function() use ($job, $historyData, $r2Service) {
            Log::debug("Bắt đầu cập nhật tiến trình hoàn thành cho job {$job->id}");
            
            // Lấy tên file ảnh từ kết quả
            $imageFilename = $this->findImageFilenameInHistoryData($historyData, $job->comfy_prompt_id);
            
            if (!$imageFilename) {
                Log::error("Không tìm thấy ảnh kết quả trong dữ liệu trả về, thử dump toàn bộ dữ liệu");
                Log::debug("Dữ liệu history đầy đủ: " . json_encode($historyData));
                
                // Kiểm tra xem historyData có chứa thông báo lỗi không
                $errorMessage = $this->extractErrorMessage($historyData, $job->comfy_prompt_id);
                
                // Cập nhật thông tin job
                $this->markJobAsFailed($job, $errorMessage);
                
                throw ExternalServiceException::comfyUIError($errorMessage);
            }
            
            // Tải ảnh kết quả từ ComfyUI
            Log::debug("Bắt đầu tải ảnh kết quả: {$imageFilename}");
            $imageData = $this->apiService->downloadResultImage($imageFilename, storage_path("app/tmp"));
            
            if (!$imageData) {
                $errorMessage = "Không thể tải ảnh kết quả từ ComfyUI";
                $this->markJobAsFailed($job, $errorMessage);
                throw ExternalServiceException::comfyUIError($errorMessage);
            }
            
            // Upload image to R2 Storage
            $resultImageUrl = $this->uploadImageToR2($job, $imageData, $imageFilename, $r2Service);
            
            // Cập nhật thông tin job
            $job->result_image = $resultImageUrl;
            $job->status = 'completed';
            $job->progress = 100;
            $job->save();
            
            // Gửi thông báo cho người dùng
            $this->sendJobNotification($job, true);
            
            Log::debug("Đã cập nhật tiến trình {$job->id} sang trạng thái hoàn thành, ảnh kết quả: {$resultImageUrl}");
            return true;
            
        }, "Updating completed job ID: {$job->id}, prompt: {$job->comfy_prompt_id}");
    }

    /**
     * Trích xuất thông báo lỗi từ dữ liệu history
     */
    protected function extractErrorMessage(array $historyData, string $promptId): string
    {
        if (isset($historyData[$promptId]['status']['status_str']) && 
            $historyData[$promptId]['status']['status_str'] === 'error') {
            return "ComfyUI báo lỗi khi xử lý: " . 
                ($historyData[$promptId]['status']['error'] ?? 'lỗi không xác định');
        } else if (isset($historyData['error'])) {
            return "Lỗi từ ComfyUI: " . $historyData['error'];
        } else {
            return "Không tìm thấy ảnh kết quả trong dữ liệu trả về từ ComfyUI";
        }
    }
    
    /**
     * Tìm tên file ảnh trong dữ liệu history
     */
    public function findImageFilenameInHistoryData(array $historyData, string $promptId): ?string
    {
        // Kiểm tra theo cấu trúc 1: {prompt_id: {outputs: {node_id: {images: [...]} } } }
        if (isset($historyData[$promptId]['outputs'])) {
            $outputs = $historyData[$promptId]['outputs'];
            Log::debug("Tìm thấy outputs trong cấu trúc 1");
            
            $filename = $this->findImageFilenameInOutputs($outputs);
            if ($filename) return $filename;
        } 
        // Cấu trúc 2: {outputs: {...}}
        elseif (isset($historyData['outputs'])) {
            $outputs = $historyData['outputs'];
            Log::debug("Tìm thấy outputs trong cấu trúc 2");
            $filename = $this->findImageFilenameInOutputs($outputs);
            if ($filename) return $filename;
        }
        
        // Kiểm tra các cấu trúc khác
        return $this->findImageFilenameInNestedData($historyData);
    }
    
    /**
     * Tìm tên file ảnh trong outputs
     */
    protected function findImageFilenameInOutputs(array $outputs): ?string
    {
        // Trước tiên tìm ảnh có type=output
        foreach ($outputs as $nodeId => $output) {
            if (isset($output['images']) && count($output['images']) > 0) {
                foreach ($output['images'] as $image) {
                    if (isset($image['type']) && $image['type'] === 'output') {
                        $filename = $image['filename'] ?? null;
                        if ($filename) {
                            Log::debug("Tìm thấy tên file ảnh output '{$filename}' từ node {$nodeId}");
                            return $filename;
                        }
                    }
                }
            }
        }
        
        // Nếu không tìm thấy, kiểm tra xem có ảnh nào không có type không
        foreach ($outputs as $nodeId => $output) {
            if (isset($output['images']) && count($output['images']) > 0) {
                foreach ($output['images'] as $image) {
                    if (!isset($image['type']) || empty($image['type'])) {
                        $filename = $image['filename'] ?? null;
                        if ($filename) {
                            Log::debug("Tìm thấy tên file ảnh không có type '{$filename}' từ node {$nodeId}");
                            return $filename;
                        }
                    }
                }
            }
        }
        
        // Cuối cùng, nếu vẫn không tìm thấy, lấy ảnh đầu tiên tìm được
        foreach ($outputs as $nodeId => $output) {
            if (isset($output['images']) && count($output['images']) > 0) {
                $filename = $output['images'][0]['filename'] ?? null;
                if ($filename) {
                    Log::debug("Tìm thấy tên file ảnh (type khác output) '{$filename}' từ node {$nodeId}");
                    return $filename;
                }
            }
        }
        
        return null;
    }

    /**
     * Tìm tên file ảnh trong dữ liệu lồng nhau
     */
    protected function findImageFilenameInNestedData(array $data): ?string
    {
        // Hàm đệ quy để tìm trong dữ liệu lồng nhau
        $findInNested = function($data, $depth = 0, $maxDepth = 4) use (&$findInNested) {
            if ($depth > $maxDepth) return null; // Giới hạn độ sâu tìm kiếm
            
            if (is_array($data)) {
                // Kiểm tra trực tiếp cho output type
                if (isset($data['images']) && !empty($data['images'])) {
                    // Ưu tiên tìm image có type=output
                    foreach ($data['images'] as $image) {
                        if (isset($image['type']) && $image['type'] === 'output' && isset($image['filename'])) {
                            Log::debug("Tìm thấy tên file ảnh output '{$image['filename']}' trong dữ liệu lồng nhau");
                            return $image['filename'];
                        }
                    }
                    
                    // Nếu không có type=output, lấy ảnh đầu tiên
                    $filename = $data['images'][0]['filename'] ?? null;
                    if ($filename) {
                        Log::debug("Tìm thấy tên file ảnh '{$filename}' trong dữ liệu lồng nhau (không có type=output)");
                        return $filename;
                    }
                }
                
                // Tìm kiếm đệ quy
                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        $result = $findInNested($value, $depth + 1, $maxDepth);
                        if ($result) return $result;
                    }
                }
            }
            return null;
        };
        
        $filename = $findInNested($data);
        if ($filename) {
            Log::debug("Tìm thấy tên file ảnh '{$filename}' trong cấu trúc dữ liệu lồng nhau");
        }
        return $filename;
    }
    
    /**
     * Kiểm tra xem dữ liệu history có chứa ảnh kết quả không
     */
    public function hasImagesInHistoryData(array $historyData): bool
    {
        // Duyệt qua dữ liệu để tìm trường images có type=output
        foreach ($historyData as $key => $value) {
            if (is_array($value)) {
                if (isset($value['images']) && !empty($value['images'])) {
                    // Ưu tiên kiểm tra ảnh có type=output
                    foreach ($value['images'] as $image) {
                        if (isset($image['type']) && $image['type'] === 'output') {
                            return true;
                        }
                    }
                }
                
                // Kiểm tra các mảng lồng nhau
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue) && isset($subValue['images']) && !empty($subValue['images'])) {
                        // Ưu tiên kiểm tra ảnh có type=output
                        foreach ($subValue['images'] as $image) {
                            if (isset($image['type']) && $image['type'] === 'output') {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        
        // Nếu không tìm thấy ảnh có type=output, thì kiểm tra bất kỳ ảnh nào
        foreach ($historyData as $key => $value) {
            if (is_array($value)) {
                if (isset($value['images']) && !empty($value['images'])) {
                    return true;
                }
                
                // Kiểm tra các mảng lồng nhau
                foreach ($value as $subKey => $subValue) {
                    if (is_array($subValue) && isset($subValue['images']) && !empty($subValue['images'])) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Kiểm tra và cập nhật trạng thái các tiến trình đang hoạt động
     */
    public function checkAndUpdatePendingJobs(R2StorageService $r2Service): bool
    {
        return $this->executeWithExceptionHandling(function() use ($r2Service) {
            // Kiểm tra trạng thái kết nối ComfyUI trước
            $comfyUIAvailable = $this->apiService->isComfyUIAvailable();
            
            if (!$comfyUIAvailable) {
                Log::warning("ComfyUI không khả dụng, bỏ qua cập nhật tiến trình");
                throw ExternalServiceException::comfyUIError('ComfyUI service unavailable');
            }
            
            Log::debug("Bắt đầu kiểm tra và cập nhật các tiến trình đang chờ xử lý");
            
            // Lấy tất cả các tiến trình đang trong trạng thái 'processing' hoặc 'pending' nhưng có comfy_prompt_id
            $jobs = ImageJob::whereIn('status', ['processing', 'pending'])
                ->whereNotNull('comfy_prompt_id')
                ->get();
            
            Log::debug("Tìm thấy {$jobs->count()} tiến trình cần kiểm tra");
            
            foreach ($jobs as $job) {
                $this->processPendingJob($job, $r2Service);
            }
            
            return true;
        }, "Checking and updating pending jobs");
    }
    
    /**
     * Xử lý một công việc đang chờ
     */
    protected function processPendingJob(ImageJob $job, R2StorageService $r2Service): void
    {
        try {
            $this->executeInTransactionSafely(function() use ($job, $r2Service) {
                Log::debug("Kiểm tra trạng thái tiến trình {$job->id} với comfy_prompt_id: {$job->comfy_prompt_id}");
                
                // Kiểm tra trực tiếp dữ liệu history
                $historyData = $this->apiService->checkJobStatus($job->comfy_prompt_id);
                
                // Kiểm tra trạng thái success/completed trong history
                $isCompleted = false;
                $isError = false;
                $errorMessage = '';
                
                // Kiểm tra xem có lỗi không
                if (isset($historyData[$job->comfy_prompt_id]['status']['status_str']) && 
                    $historyData[$job->comfy_prompt_id]['status']['status_str'] === 'error') {
                    $isError = true;
                    $errorMessage = $historyData[$job->comfy_prompt_id]['status']['error'] ?? 'Lỗi không xác định';
                    Log::error("Tiến trình {$job->id} thất bại với lỗi: {$errorMessage}");
                }
                
                // Nếu có lỗi, đánh dấu thất bại
                if ($isError) {
                    $this->markJobAsFailed($job, $errorMessage);
                    return;
                }
                
                if (!isset($historyData['error'])) {
                    // Kiểm tra các trạng thái hoàn thành
                    $isCompleted = $this->checkIfJobCompleted($historyData, $job->comfy_prompt_id);
                } 
                // Kiểm tra nếu history trả về lỗi
                else if (isset($historyData['error'])) {
                    $this->markJobAsFailed($job, 'Lỗi từ ComfyUI: ' . $historyData['error']);
                    return;
                }
                
                // Nếu đã hoàn thành theo history, cập nhật ngay
                if ($isCompleted) {
                    Log::debug("Tiến trình {$job->id} đã hoàn thành, tiến hành cập nhật");
                    $this->updateCompletedJob($job, $historyData, $r2Service);
                    return;
                }
                
                // Nếu chưa hoàn thành, tiếp tục kiểm tra tiến độ
                $this->updateJobProgress($job, $historyData, $r2Service);
                
            }, "Processing pending job ID: {$job->id}, prompt: {$job->comfy_prompt_id}");
        } catch (Exception $e) {
            // Custom error handler cho specific job
            Log::error("Lỗi khi cập nhật tiến trình {$job->id}: " . $e->getMessage());
            
            // Nếu lỗi khi kiểm tra và job đã tồn tại quá 30 phút, đánh dấu thất bại
            if ($job->created_at->diffInMinutes(now()) > 30) {
                $this->markJobAsFailed($job, 'Lỗi khi kiểm tra tiến trình: ' . $e->getMessage());
                Log::warning("Tiến trình {$job->id} đã bị đánh dấu thất bại do lỗi khi kiểm tra và quá 30 phút");
            }
        }
    }
    
    /**
     * Kiểm tra xem công việc đã hoàn thành chưa
     */
    protected function checkIfJobCompleted(array $historyData, string $promptId): bool
    {
        // Kiểm tra status
        if (isset($historyData[$promptId]['status']['status_str']) && 
            $historyData[$promptId]['status']['status_str'] === 'success') {
            Log::debug("Tiến trình đã hoàn thành (status=success)");
            return true;
        }
        
        // Kiểm tra completed flag
        if (isset($historyData[$promptId]['status']['completed']) && 
            $historyData[$promptId]['status']['completed'] === true) {
            Log::debug("Tiến trình đã hoàn thành (completed=true)");
            return true;
        }
        
        // Kiểm tra outputs có chứa images không
        if (isset($historyData[$promptId]['outputs'])) {
            foreach ($historyData[$promptId]['outputs'] as $nodeOutput) {
                if (isset($nodeOutput['images']) && !empty($nodeOutput['images'])) {
                    // Ưu tiên kiểm tra ảnh có type=output
                    $hasOutputImage = false;
                    foreach ($nodeOutput['images'] as $image) {
                        if (isset($image['type']) && $image['type'] === 'output') {
                            $hasOutputImage = true;
                            break;
                        }
                    }
                    
                    if ($hasOutputImage) {
                        Log::debug("Tiến trình đã hoàn thành (có images type=output trong outputs)");
                        return true;
                    } else if (!isset($nodeOutput['images'][0]['type'])) {
                        Log::debug("Tiến trình đã hoàn thành (có images không có type trong outputs)");
                        return true;
                    }
                }
            }
        }
        
        // Kiểm tra các trường hợp khác
        if (isset($historyData['outputs']) || $this->hasImagesInHistoryData($historyData)) {
            Log::debug("Tiến trình đã hoàn thành (kiểm tra khác)");
            return true;
        }
        
        return false;
    }
    
    /**
     * Upload image data to R2 Storage
     */
    protected function uploadImageToR2(ImageJob $job, string $imageData, string $imageFilename, R2StorageService $r2Service): string
    {
        return $this->executeWithExceptionHandling(function() use ($job, $imageData, $imageFilename, $r2Service) {
            // Đảm bảo thư mục tạm tồn tại
            $tempPath = storage_path("app/tmp");
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            // Lưu dữ liệu ảnh vào file tạm
            $email = $job->user ? $job->user->email : 'anonymous';
            $tempFilePath = $tempPath . '/' . basename($imageFilename);
            
            if (file_put_contents($tempFilePath, $imageData) === false) {
                throw ExternalServiceException::storageError('Failed to save temporary image file');
            }
            
            // Tải lên R2 Storage
            $r2Path = "uploads/generate_image/user/{$email}/" . basename($imageFilename);
            $r2Service->upload($r2Path, $tempFilePath, 'public');
            
            // Xóa file tạm
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            
            // Trả về đường dẫn đầy đủ của ảnh trên R2
            $resultImageUrl = $r2Service->getUrlR2() . '/' . $r2Path;
            Log::debug("Đã tải ảnh lên R2, URL: " . $resultImageUrl);
            
            return $resultImageUrl;
        }, "Uploading image to R2 for job ID: {$job->id}");
    }
    
    /**
     * Send job notification to user
     */
    protected function sendJobNotification(ImageJob $job, bool $success = true): void
    {
        $this->executeWithExceptionHandling(function() use ($job, $success) {
            $user = $job->user;
            if (!$user) {
                return; // No notification if no user
            }
            
            if ($success) {
                $user->notify(new ImageGeneratedNotification($job));
                Log::debug("Đã gửi thông báo hoàn thành cho người dùng {$user->id}");
            } else {
                $user->notify(new ImageGenerationFailedNotification($job));
                Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
            }
        }, "Sending " . ($success ? 'success' : 'failure') . " notification for job ID: {$job->id}");
    }
    
    /**
     * Update job progress based on API response
     */
    protected function updateJobProgress(ImageJob $job, array $historyData, R2StorageService $r2Service): void
    {
        $this->executeInTransactionSafely(function() use ($job, $historyData, $r2Service) {
            // Nếu chưa hoàn thành, tiếp tục kiểm tra tiến độ
            $progressData = $this->apiService->checkJobProgress($job->comfy_prompt_id);
            Log::debug("Tiến độ tiến trình {$job->id}: " . json_encode($progressData));
            
            // Kiểm tra nếu có lỗi từ tiến độ
            if ($progressData['status'] === 'failed' || $progressData['status'] === 'error') {
                $this->markJobAsFailed($job, $progressData['error'] ?? 'Lỗi không xác định từ ComfyUI');
                return;
            }
            
            // Luôn cập nhật tiến độ
            if (isset($progressData['progress']) && $progressData['progress'] != $job->progress) {
                $job->progress = $progressData['progress'];
                $job->save();
                Log::debug("Đã cập nhật tiến độ tiến trình {$job->id}: {$job->progress}%");
            }
            
            // Cập nhật trạng thái job dựa trên tiến độ
            if ($progressData['status'] === 'completed') {
                Log::debug("Tiến trình {$job->id} đã hoàn thành theo progress API");
                $this->updateCompletedJob($job, $historyData, $r2Service);
            } else if ($progressData['status'] === 'processing' && $job->status === 'pending') {
                // Cập nhật trạng thái từ pending sang processing
                $job->status = 'processing';
                $job->save();
                Log::debug("Đã cập nhật tiến trình {$job->id} từ pending sang processing");
            }
        }, "Updating progress for job ID: {$job->id}");
    }
    
    /**
     * Mark job as failed with error message and send notification
     */
    protected function markJobAsFailed(ImageJob $job, string $errorMessage): void
    {
        $job->status = 'failed';
        $job->error_message = $errorMessage;
        $job->save();
        
        // Gửi thông báo lỗi cho người dùng
        $this->sendJobNotification($job, false);
    }
}