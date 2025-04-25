<?php

namespace App\Services;
use App\Interfaces\LikeRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Image;
use App\Models\Interaction;
use App\Models\User;
use App\Notifications\LikeImageNotification;
use Illuminate\Support\Collection;

class LikeService
{
    protected $likeRepository;
    public function __construct(LikeRepositoryInterface $likeRepository) {
        $this->likeRepository = $likeRepository;
    }
    public function checkLiked($id)
    {
        return $this->likeRepository->checkLiked($id);
    }
    public function getLikes($id)
    {
        return $this->likeRepository->getLikes($id);
    }
    public function likePost($id)
    {
        // Kiểm tra xem ảnh có tồn tại không
        $image = $this->likeRepository->checkImageExist($id);
        if (!$image) {
            return;
        }
        // Kiểm tra xem tương tác đã tồn tại chưa
        $existingInteraction = $this->likeRepository->checkInteraction($id);
        if ($existingInteraction) {
            return;
        }
        // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();     
        try {
            // Tạo mới tương tác
            $interaction = new Interaction();
            $interaction->image_id = $id;
            $interaction->user_id = Auth::id();
            $interaction->status_interaction = 'active';
            $interaction->type_interaction = 'like';
            $interaction->save();
            
            // Cập nhật số lượt thích cho ảnh
            $image->increment('sum_like');
            
            // Lấy thông tin đầy đủ của người thích
            $liker = User::find(Auth::id());
            if ($liker) {
                $liker->increment('sum_like');
                // Gửi thông báo tới chủ ảnh (nếu không phải chính họ thích)
                if ($image->user_id !== Auth::id()) {
                    $this->sendNotification($interaction, $image);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function sendNotification($interaction, $image)
    {
         // Lấy thông tin người sở hữu ảnh
         $imageOwner = User::find($image->user_id);
         if ($imageOwner) {
             // Đảm bảo tải đầy đủ thông tin người dùng
             $fullLikerInfo = User::select('id', 'name', 'avatar_url')->find(Auth::id()); 
             // Nếu avatar_url là null, gán giá trị mặc định
             if ($fullLikerInfo && $fullLikerInfo->avatar_url === null) {
                 $fullLikerInfo->avatar_url = "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png";
             }
             // Gửi thông báo tới chủ sở hữu ảnh
             $imageOwner->notify(new LikeImageNotification($interaction, $fullLikerInfo, $image));
         }
    }
    public function unlikePost(int $id): void
    {
        // Kiểm tra xem ảnh có tồn tại không
        $image = $this->likeRepository->checkImageExist($id);
        if (!$image) {
            return;
        }
        // Tìm tương tác
        $interaction = $this->likeRepository->checkInteraction($id);
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
