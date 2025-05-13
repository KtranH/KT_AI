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

    public function generateImage($prompt, $number_out)
    {
        try {
            // Kiểm tra người dùng đang có 5 tiến trình đang hoạt động không
            if ($this->imageJobsRepository->check5Jobs(Auth::user()->id)) {
                return false;
            }
            // Logic để gọi API ComfyUI
            $time = Carbon::now();
            $result_image = null;
            $url = env('COMFYUI_URL') . '/prompt';
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                'prompt' => $prompt,
            ]);
            $prompt_id = $response->json('prompt_id');
            // Nếu thành công, tạo tiến trình
            if ($response->successful()) {
                $imageJob = $this->imageJobsRepository->createImageJob(Auth::user()->id, $prompt_id);
            }
            // Cập nhật tiến trình
            $this->imageJobsRepository->updateImageJob($imageJob->id, 'processing');
            // Kiểm tra tiến trình
            while (true) {
                $history_respone = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->get(env('COMFYUI_URL') . '/api/history/' . $prompt_id);
                if ($history_respone->successful()) {
                    $this->imageJobsRepository->updateImageJob($imageJob->id, 'done');
                    $result_image = $this->uploadToR2($prompt_id, $number_out, $history_respone->json());
                    $this->imageJobsRepository->updateResultPathImageJob($imageJob->id, $result_image);
                    break;
                }
                else if (Carbon::now()->diffInMilliseconds($time) > $this->max_time) {
                    $this->imageJobsRepository->updateFailedImageJob($imageJob->id, 'Lỗi khi tạo ảnh, quá thời gian tạo ảnh');
                    break;
                }
                sleep(2);
            }
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            $this->imageJobsRepository->updateFailedImageJob($prompt_id, 'Lỗi khi tạo ảnh, quá thời gian tạo ảnh');
            return false;
        }
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
                    if (isset($node['class_type']) && str_contains($node['class_type'], 'Image')) {
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
                    if (isset($node['class_type']) && str_contains($node['class_type'], 'Image')) {
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
     * Tải ảnh kết quả từ ComfyUI
     */
    public function downloadResultImage(string $imageFilename): ?string
    {
        try {
            // Đảm bảo đường dẫn API đúng
            $apiUrl = $this->comfyuiBaseUrl . '/view?filename=' . $imageFilename;
            Log::debug("Tải ảnh kết quả từ: " . $apiUrl);
            
            $response = Http::timeout(10)->get($apiUrl);
            
            if (!$response->successful()) {
                Log::error("API trả về lỗi khi tải ảnh: " . $response->status() . " - " . $response->body());
                throw new Exception("Lỗi khi tải ảnh kết quả: " . $response->status());
            }
            
            // Kiểm tra có phải là dữ liệu ảnh
            $contentType = $response->header('Content-Type');
            if (!$contentType || !str_contains($contentType, 'image')) {
                // Thử URL thay thế
                $alternateUrl = $this->comfyuiBaseUrl . '/api/view?filename=' . $imageFilename;
                Log::debug("URL đầu tiên không trả về ảnh, thử URL thay thế: " . $alternateUrl);
                
                $response = Http::timeout(10)->get($alternateUrl);
                
                if (!$response->successful() || !str_contains($response->header('Content-Type') ?? '', 'image')) {
                    throw new Exception("Không thể tải ảnh kết quả: phản hồi không chứa dữ liệu ảnh");
                }
            }
            
            // Đảm bảo thư mục tồn tại
            $directory = 'images/results';
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }
            
            // Lưu ảnh vào storage
            $storePath = $directory . '/' . time() . '_' . basename($imageFilename);
            $saved = Storage::put($storePath, $response->body());
            
            if (!$saved) {
                throw new Exception("Không thể lưu ảnh kết quả vào storage");
            }
            
            Log::debug("Đã lưu ảnh kết quả tại: " . $storePath);
            return $storePath;
            
        } catch (Exception $e) {
            Log::error("Lỗi khi tải ảnh kết quả: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Cập nhật tiến trình đã hoàn thành
     */
    public function updateCompletedJob(ImageJob $job, array $historyData): bool
    {
        try {
            Log::debug("Bắt đầu cập nhật tiến trình hoàn thành cho job {$job->id}");
            Log::debug("Dữ liệu lịch sử: " . json_encode($historyData));
            
            // Lấy tên file ảnh từ kết quả - hỗ trợ nhiều cấu trúc dữ liệu khác nhau
            $imageFilename = null;
            
            // Cấu trúc 1: {prompt_id: {outputs: {...}}}
            if (isset($historyData[$job->comfy_prompt_id]['outputs'])) {
                $outputs = $historyData[$job->comfy_prompt_id]['outputs'];
                Log::debug("Tìm thấy outputs trong cấu trúc 1 cho job {$job->id}");
                $imageFilename = $this->findImageFilenameInOutputs($outputs);
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
                Log::error("Không tìm thấy ảnh kết quả trong dữ liệu trả về: " . json_encode($historyData));
                throw new Exception("Không tìm thấy ảnh kết quả trong dữ liệu trả về");
            }
            
            // Tải ảnh kết quả
            Log::debug("Bắt đầu tải ảnh kết quả: {$imageFilename}");
            $resultImagePath = $this->downloadResultImage($imageFilename);
            
            if (!$resultImagePath) {
                throw new Exception("Không thể tải ảnh kết quả");
            }
            
            // Cập nhật thông tin job
            $job->result_image = $resultImagePath;
            $job->status = 'completed';
            $job->save();
            
            Log::debug("Đã cập nhật tiến trình {$job->id} sang trạng thái hoàn thành");
            return true;
            
        } catch (Exception $e) {
            Log::error("Lỗi khi cập nhật tiến trình hoàn thành: " . $e->getMessage());
            
            $job->status = 'failed';
            $job->error_message = $e->getMessage();
            $job->save();
            
            return false;
        }
    }

    /**
     * Tìm tên file ảnh trong outputs
     */
    protected function findImageFilenameInOutputs(array $outputs): ?string
    {
        foreach ($outputs as $nodeId => $output) {
            if (isset($output['images']) && count($output['images']) > 0) {
                $filename = $output['images'][0]['filename'] ?? null;
                if ($filename) {
                    Log::debug("Tìm thấy tên file ảnh '{$filename}' từ node {$nodeId}");
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
                // Kiểm tra trực tiếp
                if (isset($data['images']) && !empty($data['images'])) {
                    $filename = $data['images'][0]['filename'] ?? null;
                    if ($filename) return $filename;
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
            $apiUrl = $this->comfyuiBaseUrl . '/api/progress';
            $response = Http::get($apiUrl);
            
            if (!$response->successful()) {
                return ['status' => 'unknown', 'progress' => 0];
            }
            
            $data = $response->json();
            Log::debug("Dữ liệu tiến độ từ ComfyUI: " . json_encode($data));
            
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
            
            // Kiểm tra history để xem đã hoàn thành chưa
            $historyData = $this->checkJobStatus($comfyPromptId);
            if (!isset($historyData['error']) && 
                ($this->hasImagesInHistoryData($historyData) || 
                 isset($historyData[$comfyPromptId]['outputs']) || 
                 isset($historyData['outputs']))) {
                return ['status' => 'completed', 'progress' => 100];
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
                    
                    // Kiểm tra tiến độ của tiến trình
                    $progressData = $this->checkJobProgress($job->comfy_prompt_id);
                    Log::debug("Tiến độ tiến trình {$job->id}: " . json_encode($progressData));
                    
                    // Luôn cập nhật tiến độ
                    if (isset($progressData['progress']) && $progressData['progress'] != $job->progress) {
                        $job->progress = $progressData['progress'];
                        $job->save();
                        Log::debug("Đã cập nhật tiến độ tiến trình {$job->id}: {$job->progress}%");
                    }
                    
                    // Cập nhật trạng thái job dựa trên tiến độ
                    if ($progressData['status'] === 'completed') {
                        // Kiểm tra trạng thái từ ComfyUI
                        $historyData = $this->checkJobStatus($job->comfy_prompt_id);
                        
                        // Nếu không có lỗi và có dữ liệu outputs
                        if (!isset($historyData['error'])) {
                            if (isset($historyData[$job->comfy_prompt_id]['outputs']) || 
                                isset($historyData['outputs']) ||
                                $this->hasImagesInHistoryData($historyData)) {
                                Log::debug("Tiến trình {$job->id} có dữ liệu kết quả, tiến hành cập nhật");
                                $this->updateCompletedJob($job, $historyData);
                            } else {
                                Log::debug("Tiến trình {$job->id} được đánh dấu hoàn thành nhưng không tìm thấy dữ liệu kết quả");
                            }
                        } else {
                            Log::warning("Lỗi khi kiểm tra trạng thái tiến trình {$job->id}: " . $historyData['error']);
                        }
                    } else if ($progressData['status'] === 'processing' && $job->status === 'pending') {
                        // Cập nhật trạng thái từ pending sang processing
                        $job->status = 'processing';
                        $job->save();
                        Log::debug("Đã cập nhật tiến trình {$job->id} từ pending sang processing");
                    }
                } catch (\Exception $e) {
                    Log::error("Lỗi khi cập nhật tiến trình {$job->id}: " . $e->getMessage());
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
    protected function hasImagesInHistoryData(array $historyData): bool
    {
        // Duyệt qua dữ liệu để tìm trường images
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
