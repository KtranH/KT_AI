<?php

namespace App\Services;
use App\Interfaces\ImageRepositoryInterface;
use App\Services\R2StorageService;
use App\Http\Resources\PaginateAndRespondResource;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ImageService
{
    protected ImageRepositoryInterface $imageRepository;
    protected R2StorageService $r2StorageService;
    public function __construct(ImageRepositoryInterface $imageRepository, R2StorageService $r2StorageService)
    {
        $this->imageRepository = $imageRepository;
        $this->r2StorageService = $r2StorageService;
    }
    public function getImages(int $id)
    {
        return $this->imageRepository->getImages($id);
    }
    public function getImagesByFeature(int $featureId)
    {
        return $this->imageRepository->getImagesByFeature($featureId);
    }
    /**
     * Xử lý phân trang và trả về kết quả JSON
     *
     * @param Request $request Request object
     * @param string $typeImage Loại hình ảnh (liked, created, uploaded)
     * @param string $errorType Loại lỗi để ghi log
     * @return array
     */
    public function paginateAndRespond(Request $request, string $typeImage, string $errorType, $id = null)
    {
        try { 
            $perPage = (int)$request->input('per_page', 5);
            $page = (int)$request->input('page', 1);
            
            // Sử dụng phân trang database thay vì collection
            $paginatedImages = $this->getImagesByTypePaginated($typeImage, $id, $perPage, $page);
                        
            // Xử lý dữ liệu ảnh để đảm bảo image_url được format đúng
            $formattedItems = $paginatedImages->getCollection()->map(function($image) {
                // Nếu image_url là chuỗi JSON, chuyển đổi thành mảng
                if (is_string($image->image_url)) {
                    try {
                        $image->image_url = json_decode($image->image_url, true);
                    } catch (\Exception $e) {
                        // Nếu không thể decode, giữ nguyên
                    }
                }
                return $image;
            });
            
            return [
                'data' => $formattedItems->values()->all(),
                'pagination' => [
                    'current_page' => $paginatedImages->currentPage(),
                    'last_page' => $paginatedImages->lastPage(),
                    'per_page' => $paginatedImages->perPage(),
                    'total' => $paginatedImages->total()
                ]
            ];
        } catch (\Exception $e) {
            // Ghi log lỗi
            \Log::error("$errorType: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }
    
    // Xử lý phân loại gọi tới loại ảnh liked,created,uploaded trong db với phân trang
    private function getImagesByTypePaginated(string $typeImage, $id = null, $perPage = 5, $page = 1)
    {
        try {
            if ($typeImage === 'liked') {
                $result = $this->imageRepository->getImagesLikedPaginated($id, $perPage, $page);
                return $result;
            }
            if ($typeImage === 'created') {
                $result = $this->imageRepository->getImagesCreatedByUserPaginated($id, $perPage, $page);
                return $result;
            }
            if ($typeImage === 'uploaded') {
                $result = $this->imageRepository->getImagesUploadedPaginated($id, $perPage, $page);
                return $result;
            }
            
            // Trả về paginator rỗng nếu không có loại nào phù hợp
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Error in getImagesByTypePaginated: ' . $e->getMessage());
            throw $e;
        }
    }
    
    // Giữ lại method cũ cho backward compatibility
    private function getImagesByType(string $typeImage, $id = null)
    {
        if ($typeImage === 'liked') {
            return $this->imageRepository->getImagesLiked($id);
        }
        if ($typeImage === 'created') {
            return $this->imageRepository->getImagesCreatedByUser($id);
        }
        if ($typeImage === 'uploaded') {
            return $this->imageRepository->getImagesUploaded($id);
        }
        return collect();
    }
    
    /**
     * Kiểm tra nếu có dữ liệu mới cho user
     * 
     * @param int|null $userId ID của người dùng cần kiểm tra
     * @return bool True nếu có dữ liệu mới, False nếu không
     */
    public function checkForNewImages($userId = null)
    {
        // Nếu không có userId, sử dụng ID của user hiện tại
        $userId = $userId ?? Auth::id();
        
        if (!$userId) {
            return false;
        }
        
        // Cache key cho lần kiểm tra cuối cùng
        $lastCheckCacheKey = "user_{$userId}_last_image_check";
        
        // Lấy timestamp lần kiểm tra trước đó
        $lastCheckTimestamp = Cache::get($lastCheckCacheKey, 0);
        
        // Kiểm tra có hình ảnh mới kể từ lần kiểm tra cuối không
        $hasNewImages = Image::where('user_id', $userId)
            ->where('created_at', '>', date('Y-m-d H:i:s', $lastCheckTimestamp))
            ->exists();
        
        // Cập nhật timestamp lần kiểm tra hiện tại
        Cache::put($lastCheckCacheKey, time(), now()->addHours(24));
        
        return $hasNewImages;
    }
    
    public function storeImage(array $request, $featureId)
    {
        // Kiểm tra nếu không tìm thấy key 'images'
        if (!isset($request['images'])) {
            throw new \InvalidArgumentException("Thiếu trường 'images' trong request");
        }
        
        $files = $request['images'];
        // Duyệt qua từng file ảnh
        $uploadedPaths = [];
        foreach ($files as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = "uploads/features/{$featureId}/user/" . Auth::user()->email . '/' . $fileName;
            // Upload file và bỏ qua giá trị trả về vì chúng ta chỉ cần path
            $this->r2StorageService->upload($path, $file, 'public');
            $uploadedPaths[] = $this->r2StorageService->getUrlR2() . "/" . $path;
        }
        // Lưu trữ vào database
        $data = [
            'title' => $request['title'] ?? '',
            'description' => $request['description'] ?? '',
            'feature_id' => $featureId
        ];
        return $this->imageRepository->storeImage($uploadedPaths, Auth::user(), $data);
    }
    public function updateImage(array $request, Image $image)
    {
        return $this->imageRepository->updateImage($image, $request['title'], $request['prompt']);
    }
    public function deleteImage(Image $image): bool
    {
        return $this->imageRepository->deleteImage($image);
    }
}
