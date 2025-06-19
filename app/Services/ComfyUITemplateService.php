<?php

namespace App\Services;

use App\Models\ImageJob;
use App\Services\ComfyUIApiService;
use Illuminate\Support\Facades\Log;
use Exception;

class ComfyUITemplateService
{
    protected ComfyUIApiService $apiService;

    public function __construct(ComfyUIApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    /**
     * Tải file JSON template dựa vào feature_id
     */
    public function loadJsonTemplate(int $featureId): array
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
     * Cập nhật template JSON với ảnh tải lên
     */
    public function updateJsonTemplateWithImage(array $template, ImageJob $job): array
    {
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
        return $template;
    }
    
    /**
     * Cập nhật template JSON với thông tin từ ImageJob
     */
    public function updateJsonTemplate(array $template, ImageJob $job): array
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
} 