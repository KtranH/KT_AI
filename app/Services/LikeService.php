<?php

namespace App\Services;
use App\Interfaces\LikeRepositoryInterface;
use App\Interfaces\ImageRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Http\Resources\LikeResource;
use App\Models\Image;
use App\Models\Interaction;
use App\Notifications\LikeImageNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LikeService extends BaseService
{
    protected $likeRepository;
    protected $imageRepository;
    protected $userRepository;
    
    public function __construct(
        LikeRepositoryInterface $likeRepository,
        ImageRepositoryInterface $imageRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->likeRepository = $likeRepository;
        $this->imageRepository = $imageRepository;
        $this->userRepository = $userRepository;
    }
    
    /**
     * Kiểm tra xem ảnh đã được thích hay chưa
     *
     * @param int $id
     * @return array
     */
    public function checkLiked(int $id): array
    {
        $isLiked = $this->likeRepository->checkLiked($id);
        
        return [
            'is_liked' => $isLiked,
            'image_id' => $id
        ];
    }
    
    /**
     * Lấy danh sách likes
     *
     * @param int $id
     * @return array
     */
    public function getLikes(int $id): array
    {
        $likes = $this->likeRepository->getLikes($id);
        
        return [
            'like' => LikeResource::collection($likes),
            'count' => $likes->count()
        ];
    }
    
    /**
     * Thích một bài đăng
     *
     * @param int $id ID của hình ảnh
     * @return array Trả về thông tin like operation
     * @throws \Exception
     */
    public function likePost(int $id): array
    {
        // Kiểm tra xem ảnh có tồn tại không
        $image = $this->likeRepository->checkImageExist($id);
        
        if (!$image) {
            throw new \Exception('Không tìm thấy hình ảnh');
        }

        // Kiểm tra xem tương tác đã tồn tại chưa
        $existingInteraction = $this->likeRepository->checkInteraction($id);
        if ($existingInteraction) {
            throw new \Exception('Bạn đã thích hình ảnh này rồi');
        }

        return $this->executeInTransactionSafely(function() use ($id, $image) {
            // Tạo mới tương tác
            $interaction = $this->likeRepository->store($id, Auth::id());

            // Cập nhật số lượt thích cho ảnh
            $this->imageRepository->incrementSumLike($id);

            // Lấy thông tin đầy đủ của người thích
            $liker = $this->userRepository->findById(Auth::id());
            if ($liker) {
                $this->userRepository->increaseSumLike($image->user_id);

                // Gửi thông báo tới chủ ảnh (nếu không phải chính họ thích)
                if ($image->user_id !== Auth::id()) {
                    $this->sendNotification($interaction, $image);
                }
            }

            // Log action
            $this->logAction('Image liked', [
                'image_id' => $id,
                'user_id' => Auth::id(),
                'interaction_id' => $interaction->id
            ]);
            
            return [
                'interaction_id' => $interaction->id,
                'image_id' => $id,
                'is_liked' => true,
                'new_like_count' => $image->fresh()->sum_like
            ];
        }, "Liking image ID: $id");
    }
    
    /**
     * Gửi thông báo khi có người thích bài đăng
     *
     * @param \App\Models\Interaction $interaction Tương tác thích
     * @param \App\Models\Image $image Hình ảnh được thích
     * @return void
     */
    private function sendNotification(Interaction $interaction, Image $image): void
    {
        $this->executeWithExceptionHandling(function() use ($interaction, $image) {
            // Lấy thông tin người sở hữu ảnh
            $imageOwner = $this->userRepository->findById($image->user_id);
            if (!$imageOwner) {
                return;
            }

            // Đảm bảo tải đầy đủ thông tin người dùng
            $fullLikerInfo = $this->userRepository->getUserForNotification(Auth::id());
            if (!$fullLikerInfo) {
                return;
            }

            // Nếu avatar_url là null, gán giá trị mặc định
            if ($fullLikerInfo->avatar_url === null) {
                $fullLikerInfo->avatar_url = "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png";
            }

            // Gửi thông báo tới chủ sở hữu ảnh
            $imageOwner->notify(new LikeImageNotification($interaction, $fullLikerInfo, $image));
        }, "Sending notification for image ID: $image->id");
    }
    
    /**
     * Bỏ thích một bài đăng
     *
     * @param int $id ID của hình ảnh
     * @return array Trả về thông tin unlike operation
     * @throws \Exception
     */
    public function unlikePost(int $id): array
    {
        // Kiểm tra xem ảnh có tồn tại không
        $image = $this->likeRepository->checkImageExist($id);
        
        if (!$image) {
            throw new \Exception('Không tìm thấy hình ảnh');
        }

        // Tìm tương tác
        $interaction = $this->likeRepository->checkInteraction($id);
        if (!$interaction) {
            throw new \Exception('Bạn chưa thích hình ảnh này');
        }

        return $this->executeInTransactionSafely(function() use ($id, $image, $interaction) {
            // Xóa tương tác
            $this->likeRepository->delete($interaction);

            // Giảm số lượt thích cho ảnh nếu hiện tại > 0
            if ($image->sum_like > 0) {
                $this->imageRepository->decrementSumLike($id);
            }

            // Giảm số lượt thích cho người dùng nếu hiện tại > 0
            $user = $this->userRepository->findById(Auth::id());
            if ($user && $user->sum_like > 0) {
                $this->userRepository->decreaseSumLike($image->user_id);
            }

            // Log action
            $this->logAction('Image unliked', [
                'image_id' => $id,
                'user_id' => Auth::id()
            ]);
            
            return [
                'image_id' => $id,
                'is_liked' => false,
                'new_like_count' => $image->fresh()->sum_like
            ];
        }, "Unliking image ID: $id");
    }
}
