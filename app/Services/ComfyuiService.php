<?php

namespace App\Services;

use App\Repositories\ImageJobsRepository;
use App\Services\R2StorageService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ImageJob;
use Illuminate\Support\Facades\Storage;
use Exception;

class ComfyUIService
{
    public $max_time = 3000;
    public $imageJobsRepository;
    public $r2Service;
    protected string $comfyuiBaseUrl;

    public function __construct(ImageJobsRepository $imageJobsRepository, R2StorageService $r2Service)
    {
        $this->imageJobsRepository = $imageJobsRepository;
        $this->r2Service = $r2Service;
        $this->comfyuiBaseUrl = config('services.comfyui.url', 'http://127.0.0.1:8188');
    }
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
     * Tải file JSON template dựa vào feature_id
     */
    protected function loadJsonTemplate(int $featureId): array
    {
        $filePath = resource_path("json/{$featureId}.json");
        
        if (!file_exists($filePath)) {
            throw new Exception("Template JSON không tồn tại cho feature: {$featureId}");
        }
        
        $jsonContent = file_get_contents($filePath);
        $template = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("File JSON không hợp lệ: " . json_last_error_msg());
        }
        
        return $template;
    }
    
    /**
     * Cập nhật template JSON với thông tin từ ImageJob
     */
    protected function updateJsonTemplate(array $template, ImageJob $job): array
    {
        // Tìm node EmptyLatentImage và cập nhật width, height
        foreach ($template as $nodeId => $node) {
            if ($node['class_type'] === 'EmptyLatentImage') {
                $template[$nodeId]['inputs']['width'] = $job->width;
                $template[$nodeId]['inputs']['height'] = $job->height;
            }
            
            // Tìm node KSampler và cập nhật seed
            if (isset($node['class_type']) && str_contains($node['class_type'], 'KSampler')) {
                $template[$nodeId]['inputs']['seed'] = $job->seed;
            }
            
            // Cập nhật prompt nếu có StringFunction
            if (isset($node['class_type']) && $node['class_type'] === 'StringFunction|pysssss') {
                $template[$nodeId]['inputs']['text_a'] = $job->prompt;
                $template[$nodeId]['inputs']['result'] = $job->prompt;
            }
            
            // Cập nhật style thông qua node phù hợp nếu cần
            if ($job->style && isset($node['class_type']) && $node['class_type'] === 'StringFunction|pysssss') {
                // Nếu có style, thêm vào prompt
                $styleText = match ($job->style) {
                    'realistic' => 'realistic, photo-realistic, highly detailed',
                    'cartoon' => 'cartoon style, bright colors, exaggerated features',
                    'sketch' => 'pencil sketch, line drawing, hand-drawn',
                    'anime' => 'anime style, japanese animation, vibrant',
                    'watercolor' => 'watercolor painting, soft colors, artistic',
                    'oil-painting' => 'oil painting, textured, classic art style',
                    'digital-art' => 'digital art, modern, vibrant colors',
                    'abstract' => 'abstract art, non-representational, modern art',
                    default => '',
                };
                
                if (!empty($styleText)) {
                    $prompt = $job->prompt . ', ' . $styleText;
                    $template[$nodeId]['inputs']['text_a'] = $prompt;
                    $template[$nodeId]['inputs']['result'] = $prompt;
                }
            }
        }
        
        return $template;
    }
    
    /**
     * Tạo ảnh thông qua ComfyUI API
     */
    public function createImage(ImageJob $job): ?string
    {
        try {
            // Tải JSON template
            $template = $this->loadJsonTemplate($job->feature_id);
            
            // Cập nhật template với thông tin từ job
            $template = $this->updateJsonTemplate($template, $job);
            
            // Xử lý tải ảnh lên nếu cần
            if ($job->main_image) {
                // Tải ảnh lên ComfyUI
                $mainImageUrl = $this->uploadImageToComfyUI($job->main_image);
                
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
                $secondaryImageUrl = $this->uploadImageToComfyUI($job->secondary_image);
                
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
            $response = Http::post($this->comfyuiBaseUrl . '/prompt', [
                'prompt' => $template
            ]);
            
            if (!$response->successful()) {
                throw new Exception("Lỗi khi gửi yêu cầu đến ComfyUI: " . $response->body());
            }
            
            $responseData = $response->json();
            
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
     * Tải ảnh lên ComfyUI
     */
    protected function uploadImageToComfyUI(string $imagePath): ?string
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
            // Đảm bảo đường dẫn API đúng định dạng - sửa từ /history/ thành /api/history/
            $apiUrl = $this->comfyuiBaseUrl . '/api/history/' . $comfyPromptId;
            Log::debug("Gọi API kiểm tra job: " . $apiUrl);
            
            $response = Http::get($apiUrl);
            
            if (!$response->successful()) {
                throw new Exception("Lỗi khi kiểm tra trạng thái tiến trình: " . $response->body());
            }
            
            $data = $response->json();
            Log::debug("Dữ liệu trả về từ ComfyUI cho job {$comfyPromptId}: " . json_encode($data));
            
            return $data;
            
        } catch (Exception $e) {
            Log::error("Lỗi khi kiểm tra trạng thái: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Tải ảnh kết quả từ ComfyUI và lưu trữ trên R2
     */
    public function downloadResultImage(string $imageFilename): ?string
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
            
            // Lưu vào tệp tạm thời trước khi tải lên R2
            $user = Auth::user();
            $email = $user ? $user->email : 'anonymous';
            $tempPath = storage_path("app/tmp");
            
            // Đảm bảo thư mục tạm tồn tại
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            
            $tempFilePath = $tempPath . '/' . basename($imageFilename);
            file_put_contents($tempFilePath, $responseBody);
            
            // Tải lên R2 Storage
            $r2Path = "uploads/generate_image/user/{$email}/" . basename($imageFilename);
            $this->r2Service->upload($r2Path, $tempFilePath, 'public');
            
            // Xóa file tạm
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
            
            // Trả về đường dẫn đầy đủ của ảnh trên R2
            $r2Url = $this->r2Service->getUrlR2() . '/' . $r2Path;
            Log::debug("Đã tải ảnh lên R2, URL: " . $r2Url);
            
            return $r2Url;
            
        } catch (Exception $e) {
            Log::error("Lỗi khi tải ảnh kết quả: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cập nhật tiến trình đã hoàn thành
     */
    public function updateCompletedJob($job, array $historyData): bool
    {
        try {
            // Kiểm tra xem $job có phải là instance của ImageJob không
            if (!$job instanceof ImageJob) {
                throw new Exception("Tham số job không phải là instance của ImageJob");
            }
            
            Log::debug("Bắt đầu cập nhật tiến trình hoàn thành cho job {$job->id}");
            
            // Lấy tên file ảnh từ kết quả - hỗ trợ nhiều cấu trúc dữ liệu khác nhau
            $imageFilename = null;
            
            // Cấu trúc 1: {prompt_id: {outputs: {node_id: {images: [...]} } } }
            if (isset($historyData[$job->comfy_prompt_id]['outputs'])) {
                $outputs = $historyData[$job->comfy_prompt_id]['outputs'];
                Log::debug("Tìm thấy outputs trong cấu trúc 1 cho job {$job->id} - outputs: " . json_encode($outputs));
                
                // Ưu tiên tìm ảnh có type=output
                foreach ($outputs as $nodeId => $output) {
                    if (isset($output['images']) && count($output['images']) > 0) {
                        foreach ($output['images'] as $image) {
                            if (isset($image['type']) && $image['type'] === 'output') {
                                $imageFilename = $image['filename'] ?? null;
                                if ($imageFilename) {
                                    Log::debug("Tìm thấy tên file ảnh output '{$imageFilename}' từ node {$nodeId} và type={$image['type']}");
                                    break 2; // Thoát cả 2 vòng lặp
                                }
                            }
                        }
                    }
                }
                
                // Nếu không tìm thấy, kiểm tra lại các node
                if (!$imageFilename) {
                    foreach ($outputs as $nodeId => $output) {
                        if (isset($output['images']) && count($output['images']) > 0) {
                            $imageFilename = $output['images'][0]['filename'] ?? null;
                            Log::debug("Không tìm thấy ảnh output, sử dụng ảnh đầu tiên '{$imageFilename}' từ node {$nodeId}");
                            break;
                        }
                    }
                }
            } 
            // Cấu trúc 2: {outputs: {...}}
            elseif (isset($historyData['outputs'])) {
                $outputs = $historyData['outputs'];
                Log::debug("Tìm thấy outputs trong cấu trúc 2 cho job {$job->id}");
                $imageFilename = $this->findImageFilenameInOutputs($outputs);
            } 
            // Kiểm tra các cấu trúc khác
            else {
                Log::debug("Không tìm thấy cấu trúc outputs phổ biến, thử tìm các cấu trúc khác");
                $imageFilename = $this->findImageFilenameInNestedData($historyData);
            }
            
            if (!$imageFilename) {
                Log::error("Không tìm thấy ảnh kết quả trong dữ liệu trả về, thử dump toàn bộ dữ liệu");
                Log::debug("Dữ liệu history đầy đủ: " . json_encode($historyData));
                
                // Kiểm tra xem historyData có chứa thông báo lỗi không
                $errorMessage = null;
                if (isset($historyData[$job->comfy_prompt_id]['status']['status_str']) && 
                    $historyData[$job->comfy_prompt_id]['status']['status_str'] === 'error') {
                    $errorMessage = "ComfyUI báo lỗi khi xử lý: " . 
                        ($historyData[$job->comfy_prompt_id]['status']['error'] ?? 'lỗi không xác định');
                } else if (isset($historyData['error'])) {
                    $errorMessage = "Lỗi từ ComfyUI: " . $historyData['error'];
                } else {
                    $errorMessage = "Không tìm thấy ảnh kết quả trong dữ liệu trả về từ ComfyUI";
                }
                
                // Cập nhật thông tin job
                $job->status = 'failed';
                $job->error_message = $errorMessage;
                $job->save();
                
                // Gửi thông báo lỗi cho người dùng
                $user = $job->user;
                if ($user) {
                    $user->notify(new \App\Notifications\ImageGenerationFailedNotification($job));
                    Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
                }
                
                throw new Exception($errorMessage);
            }
            
            // Tải ảnh kết quả lên R2
            Log::debug("Bắt đầu tải ảnh kết quả: {$imageFilename}");
            $resultImageUrl = $this->downloadResultImage($imageFilename);
            
            if (!$resultImageUrl) {
                $errorMessage = "Không thể tải ảnh kết quả từ ComfyUI";
                $job->status = 'failed';
                $job->error_message = $errorMessage;
                $job->save();
                
                // Gửi thông báo lỗi cho người dùng
                $user = $job->user;
                if ($user) {
                    $user->notify(new \App\Notifications\ImageGenerationFailedNotification($job));
                    Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
                }
                
                throw new Exception($errorMessage);
            }
            
            // Cập nhật thông tin job
            $job->result_image = $resultImageUrl; // Bây giờ là đường dẫn R2 đầy đủ
            $job->status = 'completed';
            $job->progress = 100;
            $job->save();
            
            // Gửi thông báo cho người dùng
            $user = $job->user;
            if ($user) {
                $user->notify(new \App\Notifications\ImageGeneratedNotification($job));
                Log::debug("Đã gửi thông báo hoàn thành cho người dùng {$user->id}");
            }
            
            Log::debug("Đã cập nhật tiến trình {$job->id} sang trạng thái hoàn thành, ảnh kết quả: {$resultImageUrl}");
            return true;
            
        } catch (Exception $e) {
            Log::error("Lỗi khi cập nhật tiến trình hoàn thành: " . $e->getMessage());
            
            if ($job instanceof ImageJob) {
                $job->status = 'failed';
                $job->error_message = $e->getMessage();
                $job->save();
                
                // Gửi thông báo lỗi cho người dùng
                $user = $job->user;
                if ($user) {
                    $user->notify(new \App\Notifications\ImageGenerationFailedNotification($job));
                    Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
                }
            } else {
                Log::error("Không thể cập nhật trạng thái thất bại vì job không phải là instance của ImageJob");
            }
            
            return false;
        }
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
     * Kiểm tra tiến độ xử lý của một tiến trình
     */
    public function checkJobProgress(string $comfyPromptId): array
    {
        try {
            // Trước tiên kiểm tra history API để xem đã hoàn thành chưa
            $historyData = $this->checkJobStatus($comfyPromptId);
            
            // Kiểm tra xem có lỗi từ history data không
            if (isset($historyData[$comfyPromptId]['status']['status_str']) && 
                $historyData[$comfyPromptId]['status']['status_str'] === 'error') {
                $errorMessage = $historyData[$comfyPromptId]['status']['error'] ?? 'Lỗi không xác định';
                Log::error("Job {$comfyPromptId} thất bại với lỗi: {$errorMessage}");
                return [
                    'status' => 'failed',
                    'progress' => 100,
                    'error' => $errorMessage
                ];
            }
            
            // Kiểm tra trạng thái success trong history data
            if (!isset($historyData['error'])) {
                // Kiểm tra trạng thái success trong cấu trúc status
                if (isset($historyData[$comfyPromptId]['status']['status_str']) && 
                    $historyData[$comfyPromptId]['status']['status_str'] === 'success') {
                    Log::debug("Job {$comfyPromptId} đã hoàn thành theo status_str=success");
                    return ['status' => 'completed', 'progress' => 100];
                }
                
                // Kiểm tra trạng thái completed trong cấu trúc status
                if (isset($historyData[$comfyPromptId]['status']['completed']) && 
                    $historyData[$comfyPromptId]['status']['completed'] === true) {
                    Log::debug("Job {$comfyPromptId} đã hoàn thành theo completed=true");
                    return ['status' => 'completed', 'progress' => 100];
                }
                
                // Kiểm tra xem có outputs và images không
                if (isset($historyData[$comfyPromptId]['outputs'])) {
                    foreach ($historyData[$comfyPromptId]['outputs'] as $nodeOutput) {
                        if (isset($nodeOutput['images']) && !empty($nodeOutput['images'])) {
                            // Ưu tiên kiểm tra ảnh có type=output
                            $hasOutputImage = false;
                            foreach ($nodeOutput['images'] as $image) {
                                if (isset($image['type']) && $image['type'] === 'output') {
                                    $hasOutputImage = true;
                                    break;
                                }
                            }
                            
                            if ($hasOutputImage || !isset($nodeOutput['images'][0]['type'])) {
                                Log::debug("Job {$comfyPromptId} đã hoàn thành vì có images trong outputs");
                                return ['status' => 'completed', 'progress' => 100];
                            }
                        }
                    }
                }
                
                // Kiểm tra cấu trúc dữ liệu khác nếu cần
                if ($this->hasImagesInHistoryData($historyData)) {
                    Log::debug("Job {$comfyPromptId} đã hoàn thành theo hasImagesInHistoryData");
                    return ['status' => 'completed', 'progress' => 100];
                }
            } 
            // Kiểm tra nếu có lỗi từ API
            else {
                Log::error("Lỗi từ API history: " . $historyData['error']);
                return [
                    'status' => 'failed',
                    'progress' => 0,
                    'error' => $historyData['error']
                ];
            }
            
            // Nếu không tìm thấy trong history, kiểm tra progress API
            $apiUrl = $this->comfyuiBaseUrl . '/api/progress';
            $response = Http::get($apiUrl);
            
            if (!$response->successful()) {
                return ['status' => 'unknown', 'progress' => 0];
            }
            
            $data = $response->json();
            Log::debug("Dữ liệu tiến độ từ ComfyUI: " . json_encode($data));
            
            // Kiểm tra xem có lỗi trong dữ liệu tiến độ
            if (isset($data['error'])) {
                Log::error("Lỗi từ API progress: " . $data['error']);
                return [
                    'status' => 'failed',
                    'progress' => 0,
                    'error' => $data['error']
                ];
            }
            
            // Kiểm tra xem có lỗi cụ thể cho prompt_id này
            if (isset($data[$comfyPromptId]['error'])) {
                Log::error("Lỗi từ API progress cho prompt {$comfyPromptId}: " . $data[$comfyPromptId]['error']);
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
            
            // Kiểm tra lần cuối với history API
            if (!isset($historyData['error'])) {
                if (isset($historyData['outputs']) || 
                    isset($historyData[$comfyPromptId])) {
                    Log::debug("Job {$comfyPromptId} có thể đã hoàn thành theo dữ liệu history");
                    return ['status' => 'completed', 'progress' => 100];
                }
            }
            
            // Nếu không tìm thấy trong progress và không có trong history, có thể đang chờ xử lý
            return ['status' => 'pending', 'progress' => 0];
            
        } catch (Exception $e) {
            Log::error("Lỗi khi kiểm tra tiến độ: " . $e->getMessage());
            return ['status' => 'error', 'progress' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Kiểm tra và cập nhật trạng thái các tiến trình đang hoạt động
     */
    public function checkAndUpdatePendingJobs()
    {
        try {
            // Kiểm tra trạng thái kết nối ComfyUI trước
            $comfyUIAvailable = $this->isComfyUIAvailable();
            
            if (!$comfyUIAvailable) {
                Log::warning("ComfyUI không khả dụng, bỏ qua cập nhật tiến trình");
                return false;
            }
            
            Log::debug("Bắt đầu kiểm tra và cập nhật các tiến trình đang chờ xử lý");
            
            // Lấy tất cả các tiến trình đang trong trạng thái 'processing' hoặc 'pending' nhưng có comfy_prompt_id
            $jobs = ImageJob::whereIn('status', ['processing', 'pending'])
                ->whereNotNull('comfy_prompt_id')
                ->get();
            
            Log::debug("Tìm thấy {$jobs->count()} tiến trình cần kiểm tra");
            
            foreach ($jobs as $job) {
                try {
                    Log::debug("Kiểm tra trạng thái tiến trình {$job->id} với comfy_prompt_id: {$job->comfy_prompt_id}");
                    
                    // Kiểm tra trực tiếp dữ liệu history
                    $historyData = $this->checkJobStatus($job->comfy_prompt_id);
                    
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
                        $job->status = 'failed';
                        $job->error_message = $errorMessage;
                        $job->save();
                        
                        // Gửi thông báo lỗi cho người dùng
                        $user = $job->user;
                        if ($user) {
                            $user->notify(new \App\Notifications\ImageGenerationFailedNotification($job));
                            Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
                        }
                        
                        continue;
                    }
                    
                    if (!isset($historyData['error'])) {
                        // Kiểm tra status 
                        if (isset($historyData[$job->comfy_prompt_id]['status']['status_str']) && 
                            $historyData[$job->comfy_prompt_id]['status']['status_str'] === 'success') {
                            $isCompleted = true;
                            Log::debug("Tiến trình {$job->id} đã hoàn thành (status=success)");
                        }
                        
                        // Kiểm tra completed flag
                        elseif (isset($historyData[$job->comfy_prompt_id]['status']['completed']) && 
                                $historyData[$job->comfy_prompt_id]['status']['completed'] === true) {
                            $isCompleted = true;
                            Log::debug("Tiến trình {$job->id} đã hoàn thành (completed=true)");
                        }
                        
                        // Kiểm tra outputs có chứa images không
                        elseif (isset($historyData[$job->comfy_prompt_id]['outputs'])) {
                            foreach ($historyData[$job->comfy_prompt_id]['outputs'] as $nodeOutput) {
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
                                        $isCompleted = true;
                                        Log::debug("Tiến trình {$job->id} đã hoàn thành (có images type=output trong outputs)");
                                        break;
                                    } else if (!isset($nodeOutput['images'][0]['type'])) {
                                        $isCompleted = true;
                                        Log::debug("Tiến trình {$job->id} đã hoàn thành (có images không có type trong outputs)");
                                        break;
                                    }
                                }
                            }
                        }
                        
                        // Kiểm tra các trường hợp khác
                        elseif (isset($historyData['outputs']) || $this->hasImagesInHistoryData($historyData)) {
                            $isCompleted = true;
                            Log::debug("Tiến trình {$job->id} đã hoàn thành (kiểm tra khác)");
                        }
                    }
                    // Kiểm tra nếu history trả về lỗi
                    else if (isset($historyData['error'])) {
                        $job->status = 'failed';
                        $job->error_message = 'Lỗi từ ComfyUI: ' . $historyData['error'];
                        $job->save();
                        
                        // Gửi thông báo lỗi cho người dùng
                        $user = $job->user;
                        if ($user) {
                            $user->notify(new \App\Notifications\ImageGenerationFailedNotification($job));
                            Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
                        }
                        
                        Log::error("Tiến trình {$job->id} thất bại với lỗi: " . $historyData['error']);
                        continue;
                    }
                    
                    // Nếu đã hoàn thành theo history, cập nhật ngay
                    if ($isCompleted) {
                        Log::debug("Tiến trình {$job->id} đã hoàn thành, tiến hành cập nhật");
                        $this->updateCompletedJob($job, $historyData);
                        continue;
                    }
                    
                    // Nếu chưa hoàn thành, tiếp tục kiểm tra tiến độ
                    $progressData = $this->checkJobProgress($job->comfy_prompt_id);
                    Log::debug("Tiến độ tiến trình {$job->id}: " . json_encode($progressData));
                    
                    // Kiểm tra nếu có lỗi từ tiến độ
                    if ($progressData['status'] === 'failed' || $progressData['status'] === 'error') {
                        $job->status = 'failed';
                        $job->error_message = $progressData['error'] ?? 'Lỗi không xác định từ ComfyUI';
                        $job->save();
                        
                        // Gửi thông báo lỗi cho người dùng
                        $user = $job->user;
                        if ($user) {
                            $user->notify(new \App\Notifications\ImageGenerationFailedNotification($job));
                            Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
                        }
                        
                        Log::error("Tiến trình {$job->id} thất bại với lỗi: " . $job->error_message);
                        continue;
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
                        $this->updateCompletedJob($job, $historyData);
                    } else if ($progressData['status'] === 'processing' && $job->status === 'pending') {
                        // Cập nhật trạng thái từ pending sang processing
                        $job->status = 'processing';
                        $job->save();
                        Log::debug("Đã cập nhật tiến trình {$job->id} từ pending sang processing");
                    }
                } catch (\Exception $e) {
                    Log::error("Lỗi khi cập nhật tiến trình {$job->id}: " . $e->getMessage());
                    
                    // Nếu lỗi khi kiểm tra, đánh dấu job thất bại
                    if ($job->created_at->diffInMinutes(now()) > 30) {
                        $job->status = 'failed';
                        $job->error_message = 'Lỗi khi kiểm tra tiến trình: ' . $e->getMessage();
                        $job->save();
                        
                        // Gửi thông báo lỗi cho người dùng
                        $user = $job->user;
                        if ($user) {
                            $user->notify(new \App\Notifications\ImageGenerationFailedNotification($job));
                            Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
                        }
                        
                        Log::warning("Tiến trình {$job->id} đã bị đánh dấu thất bại do lỗi khi kiểm tra và quá 30 phút");
                    }
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Lỗi khi kiểm tra tiến trình: " . $e->getMessage());
            return false;
        }
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
     * Kiểm tra xem ComfyUI có khả dụng không
     */
    protected function isComfyUIAvailable(): bool
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
