<?php

namespace App\Services;

use App\Models\ImageJob;
use Illuminate\Support\Facades\Log;
use Exception;

class ComfyUITemplateService
{
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