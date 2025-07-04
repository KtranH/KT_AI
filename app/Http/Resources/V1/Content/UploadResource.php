<?php

declare(strict_types=1);

namespace App\Http\Resources\V1\Content;

use App\Http\Resources\V1\BaseResource;
use Illuminate\Http\Request;

class UploadResource extends BaseResource
{
    protected string $uploadType;
    protected ?string $originalName;
    protected ?int $fileSize;
    protected bool $success;
    protected ?string $message;

    /**
     * Tạo instance resource mới
     * 
     * @param mixed $resource
     * @param string $uploadType
     * @param string|null $originalName
     * @param int|null $fileSize
     * @param bool $success
     * @param string|null $message
     */
    public function __construct($resource, string $uploadType = 'file', ?string $originalName = null, ?int $fileSize = null, bool $success = true, ?string $message = null)
    {
        parent::__construct($resource);
        $this->uploadType = $uploadType;
        $this->originalName = $originalName;
        $this->fileSize = $fileSize;
        $this->success = $success;
        $this->message = $message;
    }

    /**
     * Chuyển đổi upload data thành mảng
     * @param Request $request Yêu cầu
     * @return array Mảng chứa thông tin upload
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message ?? ($this->success ? 'Upload thành công' : 'Upload thất bại'),
            'upload_type' => $this->uploadType,
            'file_info' => [
                'url' => $this->formatUrl($this->resource),
                'original_name' => $this->originalName,
                'file_size' => $this->fileSize,
                'file_size_formatted' => $this->fileSize ? $this->formatFileSize($this->fileSize) : null,
            ],
            'uploaded_at' => now()->format('d/m/Y H:i:s'),
            'is_public' => true,
        ];
    }

    /**
     * Tạo response cho upload avatar
     */
    public static function avatar(string $url, ?string $originalName = null, ?int $fileSize = null, string $message = 'Cập nhật avatar thành công'): self
    {
        return new static($url, 'avatar', $originalName, $fileSize, true, $message);
    }

    /**
     * Tạo response cho upload cover image
     */
    public static function coverImage(string $url, ?string $originalName = null, ?int $fileSize = null, string $message = 'Cập nhật ảnh bìa thành công'): self
    {
        return new static($url, 'cover_image', $originalName, $fileSize, true, $message);
    }

    /**
     * Tạo response cho upload image job
     */
    public static function imageJob(string $url, ?string $originalName = null, ?int $fileSize = null, string $message = 'Upload ảnh cho job thành công'): self
    {
        return new static($url, 'image_job', $originalName, $fileSize, true, $message);
    }

    /**
     * Tạo response cho upload thất bại
     */
    public static function failed(string $message = 'Upload thất bại'): array
    {
        return [
            'success' => false,
            'message' => $message,
            'upload_type' => null,
            'file_info' => null,
            'uploaded_at' => now()->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Format file size thành dạng dễ đọc
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes == 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
} 