<?php

namespace App\Services\External;

use App\Services\BaseService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class R2StorageService extends BaseService
{
    protected $disk;
    protected $urlR2;

    /**
     * Khởi tạo R2StorageService
     */
    public function __construct()
    {
        $this->disk = Storage::disk('r2');
        $this->urlR2 = env('R2_URL', '');
    }
    /**
     * Upload file lên R2
     *
     * @param string $path Đường dẫn tương đối để lưu file
     * @param mixed $file File cần upload
     * @param string $visibility Quyền truy cập file ('public' hoặc 'private')
     * @return bool Kết quả upload file
     */
    public function upload(string $path, $file, string $visibility = 'public'): bool
    {
        // Đảm bảo path không bắt đầu bằng dấu /
        $path = ltrim($path, '/');

        return $this->executeWithExceptionHandling(function() use ($path, $file, $visibility) {
            return $this->disk->put($path, file_get_contents($file), $visibility);
        }, "Uploading file to R2: {$path}");
    }

    /**
     * Lấy URL file
     *
     * @param string $path Đường dẫn tương đối của file
     * @return string URL đầy đủ của file
     */
    public function getUrl(string $path): string
    {
        // Đảm bảo path không bắt đầu bằng dấu / để tránh URL có dấu // kép
        $path = ltrim($path, '/');

        // Đảm bảo urlR2 không kết thúc bằng dấu / để tránh URL có dấu // kép
        $baseUrl = rtrim($this->urlR2, '/');

        // Trả về URL đầy đủ
        return $this->executeWithExceptionHandling(function() use ($baseUrl, $path) {
            return "{$baseUrl}/{$path}";
        }, "Getting URL for R2 file: {$path}");
    }

    /**
     * Lấy URL cơ sở của R2
     *
     * @return string URL cơ sở của R2
     */
    public function getUrlR2(): string
    {
        return rtrim($this->urlR2, '/');
    }

    /**
     * Kiểm tra file có tồn tại không
     *
     * @param string $path Đường dẫn đầy đủ hoặc tương đối của file
     * @return bool Kết quả kiểm tra tồn tại
     */
    public function exists(string $path): bool
    {
        // Nếu path chứa URL đầy đủ, loại bỏ phần URL cơ sở
        $baseUrl = rtrim($this->urlR2, '/');
        if (strpos($path, $baseUrl) === 0) {
            $path = substr($path, strlen($baseUrl));
        }

        // Đảm bảo path không bắt đầu bằng dấu /
        $path = ltrim($path, '/');

        return $this->executeWithExceptionHandling(function() use ($path) {
            return $this->disk->exists($path);
        }, "Checking if R2 file exists: {$path}");
    }

    /**
     * Xóa file khỏi R2
     *
     * @param string $path Đường dẫn đầy đủ hoặc tương đối của file
     * @return bool Kết quả xóa file
     */
    public function delete(string $path): bool
    {
        // Nếu path chứa URL đầy đủ, loại bỏ phần URL cơ sở
        $baseUrl = rtrim($this->urlR2, '/');
        if (strpos($path, $baseUrl) === 0) {
            $path = substr($path, strlen($baseUrl));
        }

        // Đảm bảo path không bắt đầu bằng dấu /
        $path = ltrim($path, '/');

        return $this->executeWithExceptionHandling(function() use ($path) {
            return $this->disk->delete($path);
        }, "Deleting R2 file: {$path}");
    }

    /**
     * Tải file từ R2
     *
     * @param string $path Đường dẫn đầy đủ hoặc tương đối của file
     * @return string Đường dẫn đầy đủ của file
     */

    public function download(string $path): string
    {
        // Nếu path chứa URL đầy đủ, loại bỏ phần URL cơ sở
        $baseUrl = rtrim($this->urlR2, '/');
        if (strpos($path, $baseUrl) === 0) {
            $path = substr($path, strlen($baseUrl));
        }

        // Đảm bảo path không bắt đầu bằng dấu /
        $path = ltrim($path, '/');

        return $this->executeWithExceptionHandling(function() use ($path) {
            return $this->disk->get($path);
        }, "Downloading R2 file: {$path}");
    }
}
