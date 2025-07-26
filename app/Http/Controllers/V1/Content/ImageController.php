<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\Content;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Http\Controllers\Constants\ErrorMessages;
use App\Http\Controllers\Constants\SuccessMessages;
use App\Http\Requests\V1\Content\UpdateImageRequest;
use App\Http\Requests\V1\Content\StoreImageRequest;
use App\Models\Image;
use App\Services\Business\ImageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ImageController extends BaseV1Controller
{
    protected ImageService $imageService;
    
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Lấy danh sách images của user
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function getImages(int $id): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->imageService->getImages($id),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }

    /**
     * Lấy images theo feature
     * 
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getImagesByFeature(int $id, Request $request): JsonResponse
    {
        $sortBy = $request->query('sort', 'newest');
        
        return $this->executeServiceMethodV1(
            fn() => $this->imageService->getImagesByFeature($id, $sortBy),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }

    /**
     * Lấy images được like bởi user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getLikedImages(Request $request): JsonResponse
    {
        $userId = $request->query('user_id');
        return $this->executeServiceMethodV1(
            fn() => $this->imageService->paginateAndRespond($request, 'liked', 'Get Images Liked Error', $userId),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }

    /**
     * Lấy images được upload bởi user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getUploadedImages(Request $request): JsonResponse
    {
        $userId = $request->query('user_id');
        return $this->executeServiceMethodV1(
            fn() => $this->imageService->paginateAndRespond($request, 'uploaded', 'Get Images Uploaded Error', $userId),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }

    /**
     * Kiểm tra images mới cho user
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkNewImages(Request $request): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->imageService->checkForNewImages($request->query('user_id')),
            null,
            ErrorMessages::IMAGE_LOAD_ERROR
        );
    }

    /**
     * Tạo image mới
     * 
     * @param StoreImageRequest $request
     * @param int $featureId
     * @return JsonResponse
     */
    public function store(StoreImageRequest $request, int $featureId): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->imageService->storeImage($request->validated(), $featureId),
            SuccessMessages::IMAGE_UPLOAD_SUCCESS,
            ErrorMessages::IMAGE_UPLOAD_ERROR
        );
    }

    /**
     * Cập nhật image
     * 
     * @param UpdateImageRequest $request
     * @param Image $image
     * @return JsonResponse
     */
    public function update(UpdateImageRequest $request, Image $image): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->imageService->updateImage($request->validated(), $image),
            SuccessMessages::IMAGE_UPDATE_SUCCESS,
            ErrorMessages::IMAGE_UPDATE_ERROR
        );
    }

    /**
     * Xóa image
     * 
     * @param Image $image
     * @return JsonResponse
     */
    public function destroy(Image $image): JsonResponse
    {
        return $this->executeServiceMethodV1(
            fn() => $this->imageService->deleteImage($image),
            SuccessMessages::IMAGE_DELETE_SUCCESS,
            ErrorMessages::IMAGE_DELETE_ERROR
        );
    }
} 