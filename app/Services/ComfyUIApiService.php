<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Exceptions\ExternalServiceException;

class ComfyUIApiService extends BaseService
{
    protected string $comfyuiBaseUrl;

    public function __construct()
    {
        $this->comfyuiBaseUrl = config('services.comfyui.url', env('COMFYUI_URL'));
    }

    /**
     * Gửi prompt đến ComfyUI để tạo ảnh
     */
    public function sendPrompt(array $template): array
    {
        return $this->executeWithExceptionHandling(function() use ($template) {
            $response = Http::post($this->comfyuiBaseUrl . '/prompt', [
                'prompt' => $template
            ]);
            
            if (!$response->successful()) {
                throw new ExternalServiceException(
                    "Lỗi khi gửi yêu cầu đến ComfyUI: " . $response->body(),
                    ['service' => 'ComfyUI', 'url' => $this->comfyuiBaseUrl]
                );
            }
            
            return $response->json();
        }, "Sending prompt to ComfyUI: " . json_encode($template));
    }
    
    /**
     * Tải ảnh lên ComfyUI
     */
    public function uploadImageToComfyUI(string $imagePath): ?string
    {
        return $this->executeWithExceptionHandling(function() use ($imagePath) {
            $response = Http::post($this->comfyuiBaseUrl . '/upload', [
                'image' => $imagePath
            ]);
            
            if (!$response->successful()) {
                throw new ExternalServiceException(
                    "Lỗi khi tải ảnh lên ComfyUI: " . $response->body(),
                    ['service' => 'ComfyUI', 'url' => $this->comfyuiBaseUrl]
                );
            }
            
            return $response->json();
        }, "Uploading image to ComfyUI: " . $imagePath);
    }
    
    /**
     * Kiểm tra trạng thái của tiến trình
     */
    public function checkJobStatus(string $comfyPromptId): array
    {
        return $this->executeWithExceptionHandling(function() use ($comfyPromptId) {
            $apiUrl = $this->comfyuiBaseUrl . '/api/history/' . $comfyPromptId;
            $response = Http::get($apiUrl);
            
            if (!$response->successful()) {    
                throw new ExternalServiceException(
                    "Lỗi khi kiểm tra trạng thái tiến trình: " . $response->body(),
                    ['service' => 'ComfyUI', 'url' => $apiUrl]
                );
            }
            
            return $response->json();
        }, "Checking job status for ComfyUI: " . $comfyPromptId);
    }
    
    /**
     * Kiểm tra tiến độ xử lý của một tiến trình
     */
    public function checkJobProgress(string $comfyPromptId): array
    {
        return $this->executeWithExceptionHandling(function() use ($comfyPromptId) {
            $apiUrl = $this->comfyuiBaseUrl . '/api/progress';
            $response = Http::get($apiUrl);
            
            if (!$response->successful()) {
                return ['status' => 'unknown', 'progress' => 0];
            }
            
            $data = $response->json();
            
            // Kiểm tra xem có lỗi trong dữ liệu tiến độ
            if (isset($data['error'])) {
                return [
                    'status' => 'failed',
                    'progress' => 0,
                    'error' => $data['error']
                ];
            }
            
            // Kiểm tra xem có lỗi cụ thể cho prompt_id này
            if (isset($data[$comfyPromptId]['error'])) {
                return [
                    'status' => 'failed',
                    'progress' => 0,
                    'error' => $data[$comfyPromptId]['error']
                ];
            }
            
            // Kiểm tra xem prompt_id có trong danh sách đang xử lý không
            if (isset($data[$comfyPromptId])) {
                // Tiến trình đang xử lý
                $progress = $data[$comfyPromptId];
                $percentage = isset($progress['value']) ? round($progress['value'] * 100) : 0;
                $status = $percentage >= 100 ? 'completed' : 'processing';
                
                return [
                    'status' => $status,
                    'progress' => $percentage,
                    'text_info' => $progress['text_info'] ?? ''
                ];
            }
            
            // Nếu không tìm thấy trong progress, có thể đang chờ xử lý
            return ['status' => 'pending', 'progress' => 0];
            
        }, "Checking job progress for ComfyUI: " . $comfyPromptId);
    }
    
    /**
     * Tải ảnh kết quả từ ComfyUI
     */
    public function downloadResultImage(string $imageFilename, string $tempPath): ?string
    {
        return $this->executeWithExceptionHandling(function() use ($imageFilename, $tempPath) {
            // Thử tải từ nhiều endpoint khác nhau
            $endpoints = [
                $this->comfyuiBaseUrl . '/view?filename=' . $imageFilename,
                $this->comfyuiBaseUrl . '/api/view?filename=' . $imageFilename,
                $this->comfyuiBaseUrl . '/output/' . $imageFilename,
                $this->comfyuiBaseUrl . '/api/download/' . $imageFilename
            ];
            
            Log::debug("Thử tải ảnh kết quả với các endpoint: " . json_encode($endpoints));
            
            $responseBody = null;
            $success = false;
            
            foreach ($endpoints as $apiUrl) {
                try {
                    Log::debug("Đang thử tải ảnh từ: " . $apiUrl);
                    $response = Http::timeout(15)->get($apiUrl);
                    
                    if ($response->successful()) {
                        // Kiểm tra có phải là dữ liệu ảnh
                        $contentType = $response->header('Content-Type');
                        if ($contentType && str_contains($contentType, 'image')) {
                            $responseBody = $response->body();
                            Log::debug("Tải ảnh thành công từ: " . $apiUrl);
                            $success = true;
                            break;
                        } else {
                            Log::debug("Endpoint trả về không phải ảnh: " . $apiUrl . " - Content-Type: " . $contentType);
                        }
                    } else {
                        Log::debug("Endpoint trả lỗi: " . $apiUrl . " - Status: " . $response->status());
                    }
                } catch (Exception $e) {
                    Log::debug("Lỗi khi thử endpoint " . $apiUrl . ": " . $e->getMessage());
                }
            }
            
            if (!$success || !$responseBody) {
                throw new Exception("Không thể tải ảnh kết quả từ bất kỳ endpoint nào");
            }
            
            return $responseBody;
        }, "Downloading result image from ComfyUI: " . $imageFilename);
    }
    
    /**
     * Kiểm tra xem ComfyUI có khả dụng không
     */
    public function isComfyUIAvailable(): bool
    {
        return $this->executeWithExceptionHandling(function() {
            $response = Http::timeout(3)->get($this->comfyuiBaseUrl);
            return $response->successful();
        }, "Checking if ComfyUI is available");
    }
} 