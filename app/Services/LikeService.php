<?php

namespace App\Services;
use App\Interfaces\LikeRepositoryInterface;
use App\Interfaces\ImageRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use App\Models\Image;
use App\Models\Interaction;
use App\Notifications\LikeImageNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
    /**
     * Thích một bài đăng
     *
     * @param int $id ID của hình ảnh
     * @return bool Trả về true nếu thành công, false nếu thất bại
     */
    public function likePost($id)
    {
        try {
            // Kiểm tra xem ảnh có tồn tại không
            $image = $this->likeRepository->checkImageExist($id);

            // Kiểm tra xem tương tác đã tồn tại chưa
            $existingInteraction = $this->likeRepository->checkInteraction($id);
            if ($existingInteraction) {
                return false;
            }

            // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::beginTransaction();

            // Tạo mới tương tác
            $interaction = $this->likeRepository->store($id, Auth::id());

            // Cập nhật số lượt thích cho ảnh
            $this->imageRepository->incrementSumLike($id);

            // Lấy thông tin đầy đủ của người thích
            $liker = User::find(Auth::id());
            if ($liker instanceof User) {
                $this->userRepository->increaseSumLike($image->user_id);

                // Gửi thông báo tới chủ ảnh (nếu không phải chính họ thích)
                if ($image->user_id !== Auth::id()) {
                    $this->sendNotification($interaction, $image);
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            \Log::error('Lỗi khi thích bài đăng: ' . $exception->getMessage());
            return false;
        }
    }
    /**
     * Gửi thông báo khi có người thích bài đăng
     *
     * @param \App\Models\Interaction $interaction Tương tác thích
     * @param \App\Models\Image $image Hình ảnh được thích
     * @return void
     */
    public function sendNotification(Interaction $interaction, Image $image)
    {
        try {
            // Lấy thông tin người sở hữu ảnh
            $imageOwner = User::find($image->user_id);
            if (!$imageOwner instanceof User) {
                return;
            }

            // Đảm bảo tải đầy đủ thông tin người dùng
            $fullLikerInfo = User::select('id', 'name', 'avatar_url')->find(Auth::id());
            if (!$fullLikerInfo instanceof User) {
                return;
            }

            // Nếu avatar_url là null, gán giá trị mặc định
            if ($fullLikerInfo->avatar_url === null) {
                $fullLikerInfo->avatar_url = "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png";
            }

            // Gửi thông báo tới chủ sở hữu ảnh
            $imageOwner->notify(new LikeImageNotification($interaction, $fullLikerInfo, $image));
        } catch (\Exception $exception) {
            \Log::error('Lỗi khi gửi thông báo: ' . $exception->getMessage());
        }
    }
    /**
     * Bỏ thích một bài đăng
     *
     * @param int $id ID của hình ảnh
     * @return bool Trả về true nếu thành công, false nếu thất bại
     */
    public function unlikePost(int $id)
    {
        try {
            // Kiểm tra xem ảnh có tồn tại không
            $image = $this->likeRepository->checkImageExist($id);

            // Tìm tương tác
            $interaction = $this->likeRepository->checkInteraction($id);
            if (!$interaction) {
                return false;
            }

            // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::beginTransaction();

            // Xóa tương tác
            $this->likeRepository->delete($interaction);

            // Giảm số lượt thích cho ảnh nếu hiện tại > 0
            if ($image->sum_like > 0) {
                $this->imageRepository->decrementSumLike($id);
            }

            // Giảm số lượt thích cho người dùng nếu hiện tại > 0
            $user = User::find(Auth::id());
            if ($user instanceof User && $user->sum_like > 0) {
                $this->userRepository->decreaseSumLike($image->user_id);
            }

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Lỗi khi bỏ thích bài đăng: ' . $exception->getMessage());
            return false;
        }
    }
}
