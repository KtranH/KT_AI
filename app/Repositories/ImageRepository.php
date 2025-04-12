<?php

namespace App\Repositories;

use App\Interfaces\ImageRepositoryInterface;
use App\Interfaces\FeatureRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Image;
use App\Models\Interaction;
use App\Models\AIFeature;
use App\Http\Resources\ImageResource;
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
        $image = Image::with('user')->with('aiFeature')->find($id);
        
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
    public function getImagesCreatedByUser(): Collection
    {
        $images = Image::with('user')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
            
        $result = collect();
        
        foreach ($images as $image) {
            $result->push([
                'id' => $image->id,
                'image_url' => is_string($image->image_url) ? json_decode($image->image_url, true) : $image->image_url,
                'prompt' => $image->prompt,
                'features_id' => $image->features_id,
                'sum_like' => $image->sum_like,
                'sum_comment' => $image->sum_comment,
                'created_at' => $image->created_at,
                'updated_at' => $image->updated_at,
                'user' => $image->user
            ]);
        }
        
        return $result;
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

    // Xóa hình ảnh
    public function deleteImage(Image $image): bool
    {
        try
        {
            // Giảm số lượng ảnh cho mục Feature
            $this->featureRepository->decreaseSumImg($image->features_id);
            // Giảm số lượng ảnh cho user
            $this->userRepository->decreaseSumImg($image->user_id);
            // Xóa hình ảnh
            $image->delete();
            return true;
        }
        catch(\Exception $e)
        {
            Log::error('Delete Image Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
            return false;
        }
    }
    
    // Cập nhật thông tin hình ảnh
    public function updateImage(Image $image, string $title, string $prompt): bool
    {
        try
        {
            $image->update([
                'prompt' => $prompt,
                'title' => $title
            ]);
            return true;
        }
        catch(\Exception $e)
        {
            Log::error('Update Image Error: ' . $e->getMessage(), [
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
    //Giảm số lượng ảnh cho mục Feature
    public function decreaseSumImg(int $featureId): void
    {
        $this->featureRepository->decreaseSumImg($featureId);
    }
    // Giảm số lượng ảnh cho user
    public function decreaseSumImgUser(int $userId): void
    {
        $this->userRepository->decreaseSumImg($userId);
    }
} 