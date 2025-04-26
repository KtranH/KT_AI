<?php

namespace App\Services;
use App\Interfaces\LikeRepositoryInterface;
use App\Interfaces\ImageRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Image;
use App\Models\Interaction;
use App\Models\User;
use App\Notifications\LikeImageNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class LikeService
{
    protected $likeRepository;
    protected $imageRepository;
    protected $userRepository;
    public function __construct(LikeRepositoryInterface $likeRepository, 
                                ImageRepositoryInterface $imageRepository, 
                                UserRepositoryInterface $userRepository) 
    {
        $this->likeRepository = $likeRepository;
        $this->imageRepository = $imageRepository;
        $this->userRepository = $userRepository;
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
            return false;
        }
        // Kiểm tra xem tương tác đã tồn tại chưa
        $existingInteraction = $this->likeRepository->checkInteraction($id);
        if ($existingInteraction) {
            return false;
        }
        // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();     
        try {
            // Tạo mới tương tác
            $this->likeRepository->store($id, Auth::id());
            // Cập nhật số lượt thích cho ảnh
            $this->imageRepository->incrementSumLike($id);
            
            // Lấy thông tin đầy đủ của người thích
            $liker = User::find(Auth::id());
            if ($liker) {
                $this->userRepository->increaseSumLike($liker);
                // Gửi thông báo tới chủ ảnh (nếu không phải chính họ thích)
                if ($image->user_id !== Auth::id()) {
                    $this->sendNotification($interaction, $image);
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
            return false;
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
    public function unlikePost(int $id)
    {
        // Kiểm tra xem ảnh có tồn tại không
        $image = $this->likeRepository->checkImageExist($id);
        if (!$image) {
            return false;
        }
        // Tìm tương tác
        $interaction = $this->likeRepository->checkInteraction($id);
        if (!$interaction) {
            return false;
        }
        // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();
        
        try {
            // Xóa tương tác
            $this->likeRepository->delete($interaction);
            // Giảm số lượt thích cho ảnh nếu hiện tại > 0
            if ($image->sum_like > 0) {
                $this->imageRepository->decreaseSumLike($image);
            }   
            // Giảm số lượt thích cho người dùng nếu hiện tại > 0
            $user = User::find(Auth::id());
            if ($user && $user->sum_like > 0) {
                $this->userRepository->decreaseSumLike($user);
            }       
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
            return false;
        }
    }
}
