<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Services\ImageService;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    protected ImageService $imageService;
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }
    public function getImages($id)
    {
        try {
            $result = $this->imageService->getImages($id);
            return response()->json([
                'success' => true,
                'images' => $result['images'],
                'data' => $result['data'],
                'user' => $result['user']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu ảnh',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getImagesByFeature($id)
    {
        try {
            $result = $this->imageService->getImagesByFeature($id);
            return response()->json([
                'success' => true,
                'data' => ImageResource::collection($result['data']),
                'pagination' => $result['pagination']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu ảnh',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function paginateAndRespond(Request $request, string $typeImage, string $errorType)
    {
        try {
            return $this->imageService->paginateAndRespond($request, $typeImage, $errorType);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu ảnh',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getImagesCreatedByUser(Request $request)
    {
        return $this->paginateAndRespond($request, 'created', 'Get Images Created By User Error');
    }
    public function getImagesLiked(Request $request)
    {
        return $this->paginateAndRespond($request, 'liked', 'Get Images Liked Error');
    }
    public function getImagesUploaded(Request $request)
    {
        return $this->paginateAndRespond($request, 'uploaded', 'Get Images Uploaded Error');
    }
    public function store(Request $request, $featureId)
    {
        try {
            // Kiểm tra xem có file được gửi lên không
            if (!$request->hasFile('images')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng tải lên ít nhất một ảnh.'
                ], 422);
            }
            $result = $this->imageService->storeImage($request, $featureId);
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tải lên thành công'
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải lên'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải lên',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
            ], 500);
        }
    }
    public function update(Request $request, Image $image)
    {
        try {
            $result = $this->imageService->updateImage($request, $image);
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật thành công'
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
            ], 500);
        }
    }
    public function destroy(Image $image)
    {
        try {
            $result = $this->imageService->deleteImage($image);
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa thành công'
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
            ], 500);
        }
    }
}