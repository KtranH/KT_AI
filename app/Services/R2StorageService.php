<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class R2StorageService
{
    protected $disk;
    protected $urlR2;

    public function __construct()
    {
        $this->disk = Storage::disk('r2');
        $this->urlR2 = env('R2_URL');
    }
    /**
     * Upload file lên R2
     */
    public function upload(string $path, $file, string $visibility = 'public')
    {
        return $this->disk->put($path, file_get_contents($file), $visibility);
    }

    /**
     * Lấy URL file
     */
    public function getUrl(string $path): string
    {
        return $this->disk->url($path);
    }

    // Lấy URL R2
    public function getUrlR2(): string
    {
        return $this->urlR2;
    }

    /**
     * Kiểm tra file có tồn tại không
     */
    public function exists(string $path): bool
    {
        return $this->disk->exists($path);
    }

    /**
     * Xóa file khỏi R2
     */
    public function delete(string $path): bool
    {
        return $this->disk->delete(str_replace($this->urlR2, '', $path));
    }
}
