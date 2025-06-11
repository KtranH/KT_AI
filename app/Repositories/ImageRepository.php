<?php

namespace App\Repositories;

use App\Interfaces\ImageRepositoryInterface;
use App\Interfaces\FeatureRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\Image;
use App\Models\Interaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


class ImageRepository implements ImageRepositoryInterface
{
    public function __construct(private readonly FeatureRepositoryInterface $featureRepository, private readonly UserRepositoryInterface $userRepository) {}
    /**
     * Lấy thông tin hình ảnh theo ID
     *
     * @param int $id ID của hình ảnh
     * @return array
     * @throws \Exception Nếu không tìm thấy hình ảnh
     */
    public function getImages(int $id): array
    {
        $image = Image::with('user')->with('aiFeature')->find($id);
        if (!$image) {
            throw new \Exception("Không tìm thấy ảnh với ID: {$id}");
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
    /**
     * Chuyển đổi danh sách hình ảnh thành collection
     *
     * @param \Illuminate\Database\Eloquent\Collection $images Danh sách hình ảnh
     * @return Collection
     */
    private function transformImages($images): Collection
    {
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

    /**
     * Lấy danh sách hình ảnh do người dùng tạo
     *
     * @return Collection
     */
    public function getImagesCreatedByUser($id = null): Collection
    {
        // Debug log
        \Log::info('ImageRepository::getImagesCreatedByUser - id: ' . ($id ?? 'null') . ', type: ' . gettype($id) . ', Auth ID: ' . (Auth::id() ?? 'null'));
        
        // Kiểm tra xem $id có hợp lệ không (int hoặc string nhưng có thể convert sang int)
        $idIsValid = $id !== null && ($id === '0' || $id === 0 || !empty($id));
        
        if ($idIsValid) {
            // Đảm bảo $id là số
            $userId = is_numeric($id) ? (int)$id : $id;
            
            $images = Image::with('user')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
                
            \Log::info('Lấy ảnh cho user ID cụ thể: ' . $userId . ', số lượng: ' . $images->count());
        } else {
            $images = Image::with('user')
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
                
            \Log::info('Lấy ảnh cho user hiện tại: ' . Auth::id() . ', số lượng: ' . $images->count());
        }

        return $this->transformImages($images);
    }

    /**
     * Lấy danh sách hình ảnh do người dùng tải lên
     *
     * @return Collection
     */
    public function getImagesUploaded($id = null): Collection
    {
        // Debug log
        \Log::info('ImageRepository::getImagesUploaded - id: ' . ($id ?? 'null') . ', Auth ID: ' . (Auth::id() ?? 'null'));
        
        // Trong trường hợp này, getImagesCreatedByUser và getImagesUploaded có cùng logic
        // Nếu sau này cần phân biệt, có thể thêm điều kiện khác
        return $this->getImagesCreatedByUser($id);
    }
    /**
     * Lấy danh sách hình ảnh đã thích
     *
     * @return Collection
     */
    public function getImagesLiked($id = null): Collection
    {
        // Debug log
        \Log::info('ImageRepository::getImagesLiked - id: ' . ($id ?? 'null') . ', type: ' . gettype($id) . ', Auth ID: ' . (Auth::id() ?? 'null'));
        
        // Kiểm tra xem $id có hợp lệ không (int hoặc string nhưng có thể convert sang int)
        $idIsValid = $id !== null && ($id === '0' || $id === 0 || !empty($id));
        
        if ($idIsValid) {
            // Đảm bảo $id là số
            $userId = is_numeric($id) ? (int)$id : $id;
            
            $list_images_liked = Interaction::where('user_id', $userId)
                ->where('type_interaction', 'like')
                ->pluck('image_id');
                
            \Log::info('Lấy ảnh đã thích cho user ID cụ thể: ' . $userId . ', số lượng: ' . $list_images_liked->count());
        } else {
            $list_images_liked = Interaction::where('user_id', Auth::id())
                ->where('type_interaction', 'like')
                ->pluck('image_id');
                
            \Log::info('Lấy ảnh đã thích cho user hiện tại: ' . Auth::id() . ', số lượng: ' . $list_images_liked->count());
        }

        $images = Image::with('user')
            ->whereIn('id', $list_images_liked)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->transformImages($images);
    }

    /**
     * Lấy danh sách hình ảnh đã thích với phân trang
     *
     * @param int|null $id User ID
     * @param int $perPage Số item mỗi trang
     * @param int $page Trang hiện tại
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getImagesLikedPaginated($id = null, int $perPage = 5, int $page = 1): \Illuminate\Pagination\LengthAwarePaginator
    {
        // Debug log
        \Log::info('ImageRepository::getImagesLikedPaginated - id: ' . ($id ?? 'null') . ' (type: ' . gettype($id) . '), page: ' . $page);
        
        // Kiểm tra xem $id có hợp lệ không - loại bỏ chuỗi rỗng và null
        $idIsValid = $id !== null && $id !== '' && trim($id) !== '' && ($id === '0' || $id === 0 || !empty($id));
        
        \Log::info('ID validation for liked - id: ' . var_export($id, true) . ', idIsValid: ' . ($idIsValid ? 'true' : 'false') . ', Auth::id(): ' . Auth::id());
        
        if ($idIsValid) {
            $userId = is_numeric($id) ? (int)$id : $id;
            $list_images_liked = Interaction::where('user_id', $userId)
                ->where('type_interaction', 'like')
                ->pluck('image_id');
        } else {
            $list_images_liked = Interaction::where('user_id', Auth::id())
                ->where('type_interaction', 'like')
                ->pluck('image_id');
        }

        // Kiểm tra nếu danh sách rỗng
        if ($list_images_liked->isEmpty()) {
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
        }

        return Image::with('user')
            ->whereIn('id', $list_images_liked)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Lấy danh sách hình ảnh do người dùng tạo với phân trang
     *
     * @param int|null $id User ID
     * @param int $perPage Số item mỗi trang
     * @param int $page Trang hiện tại
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getImagesCreatedByUserPaginated($id = null, int $perPage = 5, int $page = 1): \Illuminate\Pagination\LengthAwarePaginator
    {
        // Debug log
        \Log::info('ImageRepository::getImagesCreatedByUserPaginated - id: ' . ($id ?? 'null') . ' (type: ' . gettype($id) . '), page: ' . $page);
        
        // Kiểm tra xem $id có hợp lệ không - loại bỏ chuỗi rỗng và null
        $idIsValid = $id !== null && $id !== '' && trim($id) !== '' && ($id === '0' || $id === 0 || !empty($id));
        
        \Log::info('ID validation - id: ' . var_export($id, true) . ', idIsValid: ' . ($idIsValid ? 'true' : 'false') . ', Auth::id(): ' . Auth::id());
        
        if ($idIsValid) {
            $userId = is_numeric($id) ? (int)$id : $id;
            
            // Debug: Đếm tổng số ảnh trước khi phân trang
            $totalCount = Image::where('user_id', $userId)->count();
            \Log::info('Total images for user ' . $userId . ': ' . $totalCount);
            
            $result = Image::with('user')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
                
            \Log::info('Paginated result for user ' . $userId . ' - Count: ' . $result->count() . ', Total: ' . $result->total() . ', Page: ' . $page);
            
            return $result;
        } else {
            // Debug: Đếm tổng số ảnh trước khi phân trang
            $totalCount = Image::where('user_id', Auth::id())->count();
            \Log::info('Total images for current user ' . Auth::id() . ': ' . $totalCount);
            
            $result = Image::with('user')
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
                
            \Log::info('Paginated result for current user ' . Auth::id() . ' - Count: ' . $result->count() . ', Total: ' . $result->total() . ', Page: ' . $page);
            
            return $result;
        }
    }

    /**
     * Lấy danh sách hình ảnh do người dùng tải lên với phân trang
     *
     * @param int|null $id User ID
     * @param int $perPage Số item mỗi trang
     * @param int $page Trang hiện tại
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getImagesUploadedPaginated($id = null, int $perPage = 5, int $page = 1): \Illuminate\Pagination\LengthAwarePaginator
    {
        // Debug log
        \Log::info('ImageRepository::getImagesUploadedPaginated - id: ' . ($id ?? 'null') . ', page: ' . $page);
        
        // Trong trường hợp này, getImagesCreatedByUser và getImagesUploaded có cùng logic
        return $this->getImagesCreatedByUserPaginated($id, $perPage, $page);
    }

    /**
     * Lưu trữ hình ảnh tải lên
     */
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
        catch(\Exception $exception)
        {
            Log::error("Lỗi khi lưu trữ hình ảnh: {$exception->getMessage()}");
            return false;
        }
    }
    /**
     * Xóa hình ảnh
     *
     * @param Image $image Đối tượng hình ảnh
     * @return bool
     */
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
        catch(\Exception $exception)
        {
            Log::error("Lỗi khi xóa hình ảnh: {$exception->getMessage()}");
            return false;
        }
    }

    /**
     * Cập nhật thông tin hình ảnh
     *
     * @param Image $image Đối tượng hình ảnh
     * @param string $title Tiêu đề mới
     * @param string $prompt Mô tả mới
     * @return bool
     */
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
        catch(\Exception $exception)
        {
            Log::error("Lỗi khi cập nhật hình ảnh: {$exception->getMessage()}");
            return false;
        }
    }
    /**
     * Tăng số lượng ảnh cho mục Feature
     *
     * @param int $featureId ID của feature
     * @return void
     */
    public function increaseSumImg(int $featureId): void
    {
        try {
            $this->featureRepository->increaseSumImg($featureId);
        } catch (\Exception $exception) {
            Log::error("Lỗi khi tăng số lượng ảnh cho feature: {$exception->getMessage()}");
        }
    }

    /**
     * Tăng số lượng ảnh cho người dùng
     *
     * @param int $userId ID của người dùng
     * @return void
     */
    public function increaseSumImgUser(int $userId): void
    {
        try {
            $this->userRepository->increaseSumImg($userId);
        } catch (\Exception $exception) {
            Log::error("Lỗi khi tăng số lượng ảnh cho người dùng: {$exception->getMessage()}");
        }
    }

    /**
     * Giảm số lượng ảnh cho mục Feature
     *
     * @param int $featureId ID của feature
     * @return void
     */
    public function decreaseSumImg(int $featureId): void
    {
        try {
            $this->featureRepository->decreaseSumImg($featureId);
        } catch (\Exception $exception) {
            Log::error("Lỗi khi giảm số lượng ảnh cho feature: {$exception->getMessage()}");
        }
    }

    /**
     * Giảm số lượng ảnh cho người dùng
     *
     * @param int $userId ID của người dùng
     * @return void
     */
    public function decreaseSumImgUser(int $userId): void
    {
        try {
            $this->userRepository->decreaseSumImg($userId);
        } catch (\Exception $exception) {
            Log::error("Lỗi khi giảm số lượng ảnh cho người dùng: {$exception->getMessage()}");
        }
    }

    /**
     * Tăng số lượng like cho hình ảnh
     *
     * @param int $imageId ID của hình ảnh
     * @return void
     */
    public function incrementSumLike(int $imageId): void
    {
        try {
            $image = Image::find($imageId);
            if ($image) {
                $image->increment('sum_like');
            }
        } catch (\Exception $exception) {
            Log::error("Lỗi khi tăng số lượng like cho hình ảnh: {$exception->getMessage()}");
        }
    }

    /**
     * Giảm số lượng like cho hình ảnh
     *
     * @param int $imageId ID của hình ảnh
     * @return void
     */
    public function decrementSumLike(int $imageId): void
    {
        try {
            $image = Image::find($imageId);
            if ($image && $image->sum_like > 0) {
                $image->decrement('sum_like');
            }
        } catch (\Exception $exception) {
            Log::error("Lỗi khi giảm số lượng like cho hình ảnh: {$exception->getMessage()}");
        }
    }
}