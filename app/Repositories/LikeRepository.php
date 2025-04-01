<?php

namespace App\Repositories;

use App\Interfaces\LikeRepositoryInterface;
use App\Models\Image;
use App\Models\Interaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LikeRepository implements LikeRepositoryInterface
{
    /**
     * Thích một hình ảnh
     */
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
}
