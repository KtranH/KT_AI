<?php

namespace App\Services;

use App\Models\Image;
use App\Models\Interaction;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FeatureService;
use App\Services\UserService;

class ImageService
{
    public function __construct(private readonly FeatureService $featureService, private readonly UserService $userService) {}
    /**
     * Lấy thông tin chi tiết của một hình ảnh
     */
    public function getImage(int $id): array
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
    public function getImagesCreatedByUser(): Collection
    {
        return Image::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Kiểm tra xem người dùng đã thích hình ảnh hay chưa
     */
    public function checkLiked(int $imageId): bool
    {
        return Interaction::where('image_id', $imageId)
            ->where('user_id', Auth::id())
            ->where('type_interaction', 'like')
            ->exists();
    }
    
    /**
     * Lấy danh sách người dùng đã thích hình ảnh
     */
    public function getLikes(int $imageId, int $limit = 3): Collection
    {
        return Interaction::with('user')
            ->where('image_id', $imageId)
            ->where('type_interaction', 'like')
            ->take($limit)
            ->get();
    }
    
    /**
     * Thích một hình ảnh
     */
    public function likePost(int $imageId): void
    {
        // Kiểm tra xem ảnh có tồn tại không
        $image = Image::findOrFail($imageId);
        
        // Kiểm tra xem tương tác đã tồn tại chưa
        $existingInteraction = Interaction::where('image_id', $imageId)
            ->where('user_id', Auth::id())
            ->where('type_interaction', 'like')
            ->first();
            
        if ($existingInteraction) {
            return;
        }
        
        // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();
        
        try {
            // Tạo mới tương tác
            $interaction = new Interaction();
            $interaction->image_id = $imageId;
            $interaction->user_id = Auth::id();
            $interaction->status_interaction = 'active';
            $interaction->type_interaction = 'like';
            $interaction->save();
            
            // Cập nhật số lượt thích cho ảnh
            $image->increment('sum_like');
            
            // Cập nhật số lượt thích cho người dùng
            $user = User::find(Auth::id());
            if ($user) {
                $user->increment('sum_like');
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Like Post Error: ' . $e->getMessage(), [
                'image_id' => $imageId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Bỏ thích một hình ảnh
     */
    public function unlikePost(int $imageId): void
    {
        // Kiểm tra xem ảnh có tồn tại không
        $image = Image::findOrFail($imageId);
        
        // Tìm tương tác
        $interaction = Interaction::where('image_id', $imageId)
            ->where('user_id', Auth::id())
            ->where('type_interaction', 'like')
            ->first();
            
        if (!$interaction) {
            return;
        }
        
        // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();
        
        try {
            // Xóa tương tác
            $interaction->delete();
            
            // Giảm số lượt thích cho ảnh nếu hiện tại > 0
            if ($image->sum_like > 0) {
                $image->decrement('sum_like');
            }
            
            // Giảm số lượt thích cho người dùng nếu hiện tại > 0
            $user = User::find(Auth::id());
            if ($user && $user->sum_like > 0) {
                $user->decrement('sum_like');
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Unlike Post Error: ' . $e->getMessage(), [
                'image_id' => $imageId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    // Lưu trữ hình ảnh tải lên
    public function storeImage($uploadedPaths, $user, $data)
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
            $this->featureService->increaseSumImg($data['feature_id']);
            // Tăng số lượng ảnh cho user
            $this->userService->increaseSumImg($user->id);
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
} 