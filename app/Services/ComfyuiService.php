<?php

namespace App\Services;

use App\Repositories\ImageJobsRepository;
use App\Services\R2StorageService;
use App\Services\ComfyUITemplateService;
use App\Services\ComfyUIApiService;
use App\Services\ComfyUIJobService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\ImageJob;
use Exception;

class ComfyUIService
{
    public $max_time = 3000;
    public $imageJobsRepository;
    public $r2Service;
    protected ComfyUITemplateService $templateService;
    protected ComfyUIApiService $apiService;
    protected ComfyUIJobService $jobService;
    protected string $comfyuiBaseUrl;

    public function __construct(
        ImageJobsRepository $imageJobsRepository, 
        R2StorageService $r2Service,
        ComfyUITemplateService $templateService,
        ComfyUIApiService $apiService,
        ComfyUIJobService $jobService
    )
    {
        $this->imageJobsRepository = $imageJobsRepository;
        $this->r2Service = $r2Service;
        $this->templateService = $templateService;
        $this->apiService = $apiService;
        $this->jobService = $jobService;
        $this->comfyuiBaseUrl = config('services.comfyui.url', 'http://127.0.0.1:8188');
    }
    
    /**
     * Tải lên R2 từ ComfyUI
     */
    public function uploadToR2($prompt_id, $number_out, $data)
    {
        $filename = $data[$prompt_id]['outputs'][$number_out]['images'][0]['filename'];
        $imageUrl = env('COMFYUI_URL') . '/api/view?filename=' . $filename;
        
        // Tải ảnh từ API về (tạm lưu)
        $tempPath = storage_path("app/tmp/{$filename}");
        $imageContent = file_get_contents($imageUrl);
        file_put_contents($tempPath, $imageContent);

        // Upload ảnh lên R2
        $r2Path = "uploads/generate_image/user/" . Auth::user()->email . '/' . $filename;
        $this->r2Service->upload($r2Path, $tempPath, 'public');

        // Xóa file tạm
        unlink($tempPath);
        return $this->r2Service->getUrlR2() . "/" . $r2Path;
    }
    
    /**
     * Tạo ảnh thông qua ComfyUI API
     */
    public function createImage(ImageJob $job): ?string
    {
        try {
            // Tải JSON template và cập nhật với thông tin job
            $template = $this->templateService->loadJsonTemplate($job->feature_id);
            $template = $this->templateService->updateJsonTemplate($template, $job);
            
            // Xử lý tải ảnh lên nếu cần
            if ($job->main_image) {
                // Tải ảnh lên ComfyUI
                $mainImageUrl = $this->apiService->uploadImageToComfyUI($job->main_image);
                
                // Tìm node image loader trong template và cập nhật
                foreach ($template as $nodeId => $node) {
                    if (isset($node['class_type']) && $node['_meta']['title'] == 'Load Image First' && str_contains($node['class_type'], 'LoadImage')) {
                        $template[$nodeId]['inputs']['image'] = $mainImageUrl;
                        break;
                    }
                }
            }
            
            if ($job->secondary_image) {
                // Tương tự với ảnh phụ
                $secondaryImageUrl = $this->apiService->uploadImageToComfyUI($job->secondary_image);
                
                // Tìm node thứ hai cho ảnh phụ
                $foundFirst = false;
                foreach ($template as $nodeId => $node) {
                    if (isset($node['class_type']) && $node['_meta']['title'] == 'Load Image Second' && str_contains($node['class_type'], 'LoadImage')) {
                        if ($foundFirst) {
                            $template[$nodeId]['inputs']['image'] = $secondaryImageUrl;
                            break;
                        }
                        $foundFirst = true;
                    }
                }
            }
            
            // Gửi template đến ComfyUI để tạo ảnh
            $responseData = $this->apiService->sendPrompt($template);
            
            // Lưu ID prompt từ ComfyUI để theo dõi tiến trình
            $job->comfy_prompt_id = $responseData['prompt_id'] ?? null;
            $job->status = 'processing';
            $job->save();
            
            return $responseData['prompt_id'] ?? null;
            
        } catch (Exception $e) {
            Log::error("Lỗi khi tạo ảnh với ComfyUI: " . $e->getMessage());
            
            $job->status = 'failed';
            $job->error_message = $e->getMessage();
            $job->save();
            
            return null;
        }
    }
    
    /**
     * Kiểm tra trạng thái của tiến trình
     */
    public function checkJobStatus(string $comfyPromptId): array
    {
        return $this->apiService->checkJobStatus($comfyPromptId);
    }
    
    /**
     * Lấy thông tin tiến độ xử lý của một tiến trình
     */
    public function getJobProgress(string $comfyPromptId): array
    {
        return $this->apiService->checkJobProgress($comfyPromptId);
    }
    
    /**
     * Cập nhật tiến trình đã hoàn thành
     */
    public function updateCompletedJob(ImageJob $job, array $historyData): bool
    {
        return $this->jobService->updateCompletedJob($job, $historyData, $this->r2Service);
    }
    
    /**
     * Kiểm tra và cập nhật trạng thái các tiến trình đang hoạt động
     */
    public function checkAndUpdatePendingJobs()
    {
        return $this->jobService->checkAndUpdatePendingJobs($this->r2Service);
    }
}
