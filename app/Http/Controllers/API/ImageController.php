<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ErrorMessages;
use App\Http\Controllers\SuccessMessages;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Http\Requests\Image\UpdateImageRequest;
use App\Http\Requests\Image\StoreImageRequest;
use App\Services\Business\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class ImageController extends Controller
{
    protected ImageService $imageService;
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    /**
     * Lấy tất cả các ảnh
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getImages(int $id): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->imageService->getImages($id),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }
    /**
     * Lấy tất cả các ảnh theo chức năng
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getImagesByFeature(int $id): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->imageService->getImagesByFeature($id),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }
    /**
     * Lấy tất cả các ảnh theo loại
     * 
     * @param Request $request
     * @param string $typeImage
     * @param string $errorType
     * @param int|null $userId
     * @return JsonResponse
     */
    public function paginateAndRespond(Request $request, string $typeImage, string $errorType, $userId = null): JsonResponse
    {
        // Lấy user_id từ request query
        $userId = $request->query('user_id');
        return $this->executeServiceMethod(
            fn() => $this->imageService->paginateAndRespond($request, $typeImage, $errorType, $userId),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }
    /**
     * Lấy tất cả các ảnh được thích
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getImagesLiked(Request $request): JsonResponse
    {
        // Lấy user_id từ request query
        $userId = $request->query('user_id');
        return $this->executeServiceMethod(
            fn() => $this->imageService->paginateAndRespond($request, 'liked', 'Get Images Liked Error', $userId),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }
    /**
     * Lấy tất cả các ảnh được tải lên
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getImagesUploaded(Request $request): JsonResponse
    {
        // Ưu tiên id từ path, nếu không có thì lấy từ query
        $userId = $request->query('user_id');
        return $this->executeServiceMethod(
            fn() => $this->imageService->paginateAndRespond($request, 'uploaded', 'Get Images Uploaded Error', $userId),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }
    
    /**
     * Kiểm tra nếu có hình ảnh mới cho user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkNewImages(Request $request): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->imageService->checkForNewImages($request->query('user_id')),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }
    
    /**
     * Lưu một ảnh mới
     * 
     * @param StoreImageRequest $request
     * @param int $featureId
     * @return JsonResponse
     */
    public function store(StoreImageRequest $request, $featureId)
    {
        return $this->executeServiceMethod(
            fn() => $this->imageService->storeImage($request->validated(), $featureId),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }
    /**
     * Cập nhật một ảnh
     * 
     * @param UpdateImageRequest $request
     * @param Image $image
     * @return JsonResponse
     */
    public function update(UpdateImageRequest $request, Image $image): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->imageService->updateImage($request->validated(), $image),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }
    /**
     * Xóa một ảnh
     * 
     * @param Image $image
     * @return JsonResponse
     */
    public function destroy(Image $image): JsonResponse
    {
        return $this->executeServiceMethod(
            fn() => $this->imageService->deleteImage($image),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }
}