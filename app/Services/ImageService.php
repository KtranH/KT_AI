<?php

namespace App\Services;
use App\Interfaces\ImageRepositoryInterface;
use App\Http\Requests\Image\StoreImageRequest;
use App\Services\R2StorageService;
use App\Http\Resources\PaginateAnRespondResource;
use App\Http\Requests\Image\UpdateImageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ImageService
{
    protected ImageRepositoryInterface $imageRepository;
    protected R2StorageService $r2StorageService;
    public function __construct(ImageRepositoryInterface $imageRepository, R2StorageService $r2StorageService)
    {
        $this->imageRepository = $imageRepository;
        $this->r2StorageService = $r2StorageService;
    }
    public function getImages(int $id)
    {
        return $this->imageRepository->getImages($id);
    }
    public function getImagesByFeature(int $featureId)
    {
        return $this->imageRepository->getImagesByFeature($featureId);
    }
    /**
     * Xử lý phân trang và trả về kết quả JSON
     *
     * @param Collection $images Danh sách hình ảnh
     * @param int $page Trang hiện tại
     * @param int $perPage Số lượng mục trên mỗi trang
     * @param string $errorType Loại lỗi để ghi log
     * @return JsonResponse
     */
    public function paginateAndRespond(Request $request, string $typeImage, string $errorType)
    {
        $perPage = (int)$request->input('per_page', 5);
        $page = (int)$request->input('page', 1);
        $images = $this->getImagesByType($typeImage);

        // Nếu không có ảnh nào
        if ($images->isEmpty()) {
            return new PaginateAnRespondResource($images);
        }

        // Phân trang sử dụng LengthAwarePaginator thay vì thủ công
        $totalItems = $images->count();
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $images->slice($offset, $perPage)->values();

        // Tạo một LengthAwarePaginator object
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $totalItems,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return new PaginateAnRespondResource($paginator);
    }
    // Xử lý phân loại gọi tới loại ảnh liked,created,uploaded trong db
    private function getImagesByType(string $typeImage)
    {
        if ($typeImage === 'liked') {
            return $this->imageRepository->getImagesLiked();
        }
        if ($typeImage === 'created') {
            return $this->imageRepository->getImagesCreatedByUser();
        }
        if ($typeImage === 'uploaded') {
            return $this->imageRepository->getImagesUploaded();
        }
        return collect();
    }
    public function storeImage(Request $request, $featureId)
    {
        $storeImageRequest = new StoreImageRequest();
        $request->validate($storeImageRequest->rules());
        $files = $request->file('images');
        // Duyệt qua từng file ảnh
        $uploadedPaths = [];
        foreach ($files as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = "uploads/features/{$featureId}/user/" . Auth::user()->email . '/' . $fileName;
            $filePath = $this->r2StorageService->upload($path, $file, 'public');
            $uploadedPaths[] = $this->r2StorageService->getUrlR2() . "/" . $path;
        }
        // Lưu trữ vào database
        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'feature_id' => $featureId
        ];
        return $this->imageRepository->storeImage($uploadedPaths, Auth::user(), $data);
    }
    public function updateImage(Request $request, Image $image)
    {
        if(Gate::denies('update', $image)) {
            return false;
        }
        $updateImageRequest = new UpdateImageRequest();
        $request->validate($updateImageRequest->rules());
        return $this->imageRepository->updateImage($image, $request->title, $request->prompt);
    }
    public function deleteImage(Image $image): bool
    {
        if(Gate::denies('delete', $image)) {
            return false;
        }
        return $this->imageRepository->deleteImage($image);
    }
}
