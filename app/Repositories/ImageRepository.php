<?php

namespace App\Repositories;

use App\Interfaces\ImageRepositoryInterface;
use App\Interfaces\FeatureRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Image;
use App\Models\Interaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ImageRepository implements ImageRepositoryInterface
{
    public function __construct(private readonly FeatureRepositoryInterface $featureRepository, private readonly UserRepositoryInterface $userRepository) {}
    /**
     * Lấy thông tin chi tiết của một hình ảnh
     */
    public function getImages(int $id): array
    {
        $image = Image::with('user')->find($id);
        
        if (!$image) {
            throw new \Exception('Không tìm thấy ảnh với ID: ' . $id);
        }
        
        return [
            'success' => true,
            'images' => is_string($image->image_url) ? json_decode($image->image_url, true) : $image->image_url,
            'data' => $image,
            'user' => $image->user
        ];
    }
    
    /**
     * Lấy danh sách hình ảnh theo feature
     */
    public function getImagesByFeature(int $featureId, int $perPage = 5): array
    {
        $images = Image::with('user')
            ->where('features_id', $featureId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
            
        return [
            'data' => $images,
            'pagination' => [
                'current_page' => $images->currentPage(),
                'last_page' => $images->lastPage(),
                'per_page' => $images->perPage(),
                'total' => $images->total()
            ]
        ];
    }
    
    /**
     * Lấy danh sách hình ảnh của người dùng hiện tại
     */
    public function getImagesCreatedByUser(int $perPage = 5): array
    {
        $images = Image::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
            
        return [
            'data' => $images,
            'pagination' => [
                'current_page' => $images->currentPage(),
                'last_page' => $images->lastPage(),
                'per_page' => $images->perPage(),
                'total' => $images->total()
            ]
        ];
    }
    
    // Lưu trữ hình ảnh tải lên
    public function storeImage($uploadedPaths, $user, $data): bool
    {
        try
        {
            Image::create([
                'user_id' => $user->id,
                'image_url' => json_encode($uploadedPaths),
                'prompt' => $data['title'],
                //'description' => $data['description'],
                'features_id' => $data['feature_id'],
                'status_image' => 'completed',
                'sum_like' => 0,
                'sum_comment' => 0,
                'privacy_status' => 'public',
                'metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Tăng số lượng ảnh cho mục Feature
            $this->featureRepository->increaseSumImg($data['feature_id']);
            // Tăng số lượng ảnh cho user
            $this->userRepository->increaseSumImg($user->id);
            return true;
        }
        catch(\Exception $e)
        {
            Log::error('Store Image Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
            return false;
        }
    }
    // Tăng số lượng ảnh cho mục Feature
    public function increaseSumImg(int $featureId): void
    {
        $this->featureRepository->increaseSumImg($featureId);
    }
    // Tăng số lượng ảnh cho user
    public function increaseSumImgUser(int $userId): void
    {
        $this->userRepository->increaseSumImg($userId);
    }
    /*// Giảm số lượng ảnh cho mục Feature
    public function decreaseSumImg(int $featureId): void
    {
        $this->featureService->decreaseSumImg($featureId);
    }
    // Giảm số lượng ảnh cho user
    public function decreaseSumImgUser(int $userId): void
    {
        $this->userService->decreaseSumImg($userId);
    }*/
} 