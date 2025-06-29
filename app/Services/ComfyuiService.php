<?php

namespace App\Services;

use App\Repositories\ImageJobsRepository;
use App\Services\R2StorageService;
use App\Services\ComfyUITemplateService;
use App\Services\ComfyUIApiService;
use App\Services\ComfyUIJobService;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ImageJob;
use Exception;
use Illuminate\Support\Facades\Http;

class ComfyUIService extends BaseService
{
    public $max_time = 3000;
    public $imageJobsRepository;
    public $r2Service;
    public UserRepositoryInterface $userRepository;
    protected ComfyUITemplateService $templateService;
    protected ComfyUIApiService $apiService;
    protected ComfyUIJobService $jobService;
    protected string $comfyuiBaseUrl;

    public function __construct(
        ImageJobsRepository $imageJobsRepository, 
        R2StorageService $r2Service,
        ComfyUITemplateService $templateService,
        ComfyUIApiService $apiService,
        ComfyUIJobService $jobService,
        UserRepositoryInterface $userRepository
    )
    {
        $this->userRepository = $userRepository;
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
        return $this->executeWithExceptionHandling(function() use ($prompt_id, $number_out, $data) {
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
        }, "Uploading to R2 for prompt ID: {$prompt_id}");
    }
    
    /**
     * Tạo ảnh thông qua ComfyUI API
     */
    public function createImage(ImageJob $job): ?string
    {
        return $this->executeInTransactionSafely(function() use ($job) {
            // Tải JSON template và cập nhật với thông tin job
            $template = $this->templateService->loadJsonTemplate($job->feature_id);
            $template = $this->templateService->updateJsonTemplate($template, $job);
            
            // Tải JSON template với ảnh tải lên
            $template = $this->templateService->updateJsonTemplateWithImage($template, $job);
            
            // Gửi template đến ComfyUI để tạo ảnh
            $responseData = $this->apiService->sendPrompt($template);
            
            // Lưu ID prompt từ ComfyUI để theo dõi tiến trình
            $job->comfy_prompt_id = $responseData['prompt_id'] ?? null;
            $job->status = 'processing';
            $job->save();
            
            return $responseData['prompt_id'] ?? null;
        }, "Creating image with ComfyUI for job ID: {$job->id}");
    }
    
    /**
     * Kiểm tra trạng thái của tiến trình
     */
    public function checkJobStatus(string $comfyPromptId): array
    {
        return $this->executeWithExceptionHandling(function() use ($comfyPromptId) {
            return $this->apiService->checkJobStatus($comfyPromptId);
        }, "Checking job status for ComfyUI prompt ID: {$comfyPromptId}");
    }
    
    /**
     * Kiểm tra tiến trình của job
     * 
     * @param string $promptId ID của prompt gửi đến ComfyUI
     * @return array Mảng chứa thông tin về tiến trình (status, progress, error)
     */
    public function checkJobProgress(string $promptId): array
    {
        return $this->executeWithExceptionHandling(function() use ($promptId) {
            // Endpoint API để kiểm tra tiến trình
            $url = $this->comfyuiBaseUrl . "/history/{$promptId}";
            
            $response = Http::timeout(30)->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Kiểm tra xem đã hoàn thành chưa
                if (isset($data[$promptId]['status']['completed']) && $data[$promptId]['status']['completed'] === true) {
                    return [
                        'status' => 'completed',
                        'progress' => 100
                    ];
                }
                
                // Kiểm tra lỗi
                if (isset($data[$promptId]['status']['status_str']) && $data[$promptId]['status']['status_str'] === 'error') {
                    return [
                        'status' => 'error',
                        'progress' => 0,
                        'error' => $data[$promptId]['status']['error'] ?? 'Lỗi không xác định'
                    ];
                }
                
                // Lấy tiến trình nếu có
                $progress = 0;
                if (isset($data[$promptId]['status']['progress'])) {
                    $progress = (float) $data[$promptId]['status']['progress'] * 100;
                }
                
                return [
                    'status' => 'processing',
                    'progress' => $progress
                ];
            } else {
                // Xử lý lỗi HTTP
                Log::error('Lỗi khi kiểm tra tiến trình job: ' . $response->body());
                return [
                    'status' => 'failed',
                    'progress' => 0,
                    'error' => 'HTTP Error: ' . $response->status()
                ];
            }
        }, "Checking job progress for ComfyUI prompt ID: {$promptId}");
    }
    
    /**
     * Cập nhật tiến trình đã hoàn thành
     */
    public function updateCompletedJob(ImageJob $job, array $historyData): bool
    {
        return $this->executeInTransactionSafely(function() use ($job, $historyData) {
            return $this->jobService->updateCompletedJob($job, $historyData, $this->r2Service);
        }, "Updating completed job for job ID: {$job->id}");
    }
    
    /**
     * Kiểm tra và cập nhật trạng thái các tiến trình đang hoạt động
     */
    public function checkAndUpdatePendingJobs()
    {
        return $this->executeInTransactionSafely(function() {
            return $this->jobService->checkAndUpdatePendingJobs($this->r2Service);
        }, "Checking and updating pending jobs");
    }

    /**
     * Kiểm tra có hình ảnh trong dữ liệu lịch sử không
     * 
     * @param array $historyData Dữ liệu lịch sử từ ComfyUI
     * @return bool Có hình ảnh hay không
     */
    public function hasImagesInHistoryData(array $historyData): bool
    {
        return $this->executeWithExceptionHandling(function() use ($historyData) {
        // Kiểm tra trong mỗi prompt ID
        foreach ($historyData as $promptId => $data) {
            // Bỏ qua các khóa không phải là prompt ID
            if ($promptId === 'error' || !is_array($data)) {
                continue;
            }
            
            // Kiểm tra trong outputs
            if (isset($data['outputs'])) {
                foreach ($data['outputs'] as $nodeOutput) {
                    if (isset($nodeOutput['images']) && !empty($nodeOutput['images'])) {
                        return true;
                    }
                }
            }
        }
        
        return false;
        }, "Checking for images in history data");
    }
}
