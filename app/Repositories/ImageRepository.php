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
    public function getImagesUploaded(): Collection
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
    public function getImagesLiked(): Collection
    {
        $list_images_liked = Interaction::where('user_id', Auth::id())
            ->where('type_interaction', 'like')
            ->pluck('image_id');
        $images = Image::with('user')
            ->whereIn('id', $list_images_liked)
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
    public function storeImage($uploadedPaths, $user, $data): bool
    {
        try
        {
            Image::create([
                'user_id' => $user->id,
                'image_url' => json_encode($uploadedPaths),
                'title' => $data['title'],
                'prompt' => $data['description'],
                'features_id' => $data['feature_id'],
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
            return false;
        }
    }
    public function increaseSumImg(int $featureId): void
    {
        $this->featureRepository->increaseSumImg($featureId);
    }
    public function increaseSumImgUser(int $userId): void
    {
        $this->userRepository->increaseSumImg($userId);
    }
    public function decreaseSumImg(int $featureId): void
    {
        $this->featureRepository->decreaseSumImg($featureId);
    }
    public function decreaseSumImgUser(int $userId): void
    {
        $this->userRepository->decreaseSumImg($userId);
    }
    public function incrementSumLike(int $imageId): void
    {
        $image = Image::find($imageId);
        if ($image) {
            $image->increment('sum_like');
        }
    }
    public function decrementSumLike(int $imageId): void
    {
        $image = Image::find($imageId);
        if ($image) {
            $image->decrement('sum_like');
        }
    }
} 