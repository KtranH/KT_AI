<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class ComfyUIApiService
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
        $response = Http::post($this->comfyuiBaseUrl . '/prompt', [
            'prompt' => $template
        ]);
        
        if (!$response->successful()) {
            throw new Exception("Lỗi khi gửi yêu cầu đến ComfyUI: " . $response->body());
        }
        
        return $response->json();
    }
    
    /**
     * Tải ảnh lên ComfyUI
     */
    public function uploadImageToComfyUI(string $imagePath): ?string
    {
        try {
            $filePath = Storage::path($imagePath);
            
            $response = Http::attach(
                'image', 
                file_get_contents($filePath), 
                basename($filePath)
            )->post($this->comfyuiBaseUrl . '/upload/image');
            
            if (!$response->successful()) {
                throw new Exception("Lỗi khi tải ảnh lên ComfyUI: " . $response->body());
            }
            
            $responseData = $response->json();
            return $responseData['name'] ?? null;
            
        } catch (Exception $e) {
            Log::error("Lỗi khi tải ảnh lên ComfyUI: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Kiểm tra trạng thái của tiến trình
     */
    public function checkJobStatus(string $comfyPromptId): array
    {
        try {
            $apiUrl = $this->comfyuiBaseUrl . '/api/history/' . $comfyPromptId;
            Log::debug("Gọi API kiểm tra job: " . $apiUrl);
            
            $response = Http::get($apiUrl);
            
            if (!$response->successful()) {
                throw new Exception("Lỗi khi kiểm tra trạng thái tiến trình: " . $response->body());
            }
            
            return $response->json();
            
        } catch (Exception $e) {
            Log::error("Lỗi khi kiểm tra trạng thái: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Kiểm tra tiến độ xử lý của một tiến trình
     */
    public function checkJobProgress(string $comfyPromptId): array
    {
        try {
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
            
        } catch (Exception $e) {
            Log::error("Lỗi khi kiểm tra tiến độ: " . $e->getMessage());
            return ['status' => 'error', 'progress' => 0, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Tải ảnh kết quả từ ComfyUI
     */
    public function downloadResultImage(string $imageFilename, string $tempPath): ?string
    {
        try {
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
                } catch (\Exception $e) {
                    Log::debug("Lỗi khi thử endpoint " . $apiUrl . ": " . $e->getMessage());
                }
            }
            
            if (!$success || !$responseBody) {
                throw new Exception("Không thể tải ảnh kết quả từ bất kỳ endpoint nào");
            }
            
            return $responseBody;
            
        } catch (Exception $e) {
            Log::error("Lỗi khi tải ảnh kết quả: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Kiểm tra xem ComfyUI có khả dụng không
     */
    public function isComfyUIAvailable(): bool
    {
        try {
            $response = Http::timeout(3)->get($this->comfyuiBaseUrl);
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning("Không thể kết nối tới ComfyUI: " . $e->getMessage());
            return false;
        }
    }
} 