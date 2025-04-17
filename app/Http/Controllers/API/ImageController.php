<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Services\R2StorageService;
use App\Repositories\ImageRepository;
use App\Policies\ImagePolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class ImageController extends Controller
{
    public function __construct(private readonly ImageRepository $imageRepository, 
                                private readonly R2StorageService $r2StorageService) {}

    public function getImages($id): JsonResponse
    {
        try {
            $result = $this->imageRepository->getImages($id);
            
            return response()->json([
                'success' => true,
                'images' => $result['images'],
                'data' => $result['data'],
                'user' => $result['user']
            ]);
        } catch (\Exception $e) {
            Log::error('Get Images Error: ' . $e->getMessage(), [
                'image_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu ảnh',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getImagesByFeature($id): JsonResponse
    {
        try {
            $result = $this->imageRepository->getImagesByFeature($id);
            
            return response()->json([
                'success' => true,
                'data' => ImageResource::collection($result['data']),
                'pagination' => $result['pagination']
            ]);
        } catch (\Exception $e) {
            Log::error('Get Images By Feature Error: ' . $e->getMessage(), [
                'feature_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu ảnh',
                'error' => $e->getMessage()
            ], 500);
        }
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
    private function paginateAndRespond($images, int $page, int $perPage, string $errorType): JsonResponse
    {
        try {
            // Nếu không có ảnh nào
            if ($images->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'pagination' => [
                        'current_page' => $page,
                        'last_page' => 1,
                        'per_page' => $perPage,
                        'total' => 0
                    ]
                ]);
            }
            
            // Phân trang thủ công
            $totalItems = $images->count();
            $offset = ($page - 1) * $perPage;
            $paginatedData = $images->slice($offset, $perPage)->values()->all();
            
            return response()->json([
                'success' => true,
                'data' => $paginatedData,
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => ceil($totalItems / $perPage),
                    'per_page' => $perPage,
                    'total' => $totalItems
                ]
            ]);
        } catch (\Exception $e) {
            Log::error($errorType . ': ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu ảnh',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Lấy danh sách ảnh người dùng tạo
    public function getImagesCreatedByUser(Request $request): JsonResponse
    {
        $perPage = (int)$request->input('per_page', 5);
        $page = (int)$request->input('page', 1);
        $images = $this->imageRepository->getImagesCreatedByUser();
        
        return $this->paginateAndRespond($images, $page, $perPage, 'Get Images Created By User Error');
    }
    
    // Lấy danh sách ảnh đã thích
    public function getImagesLiked(Request $request): JsonResponse
    {
        $perPage = (int)$request->input('per_page', 5);
        $page = (int)$request->input('page', 1);
        $images = $this->imageRepository->getImagesLiked();
        
        return $this->paginateAndRespond($images, $page, $perPage, 'Get Images Liked Error');
    }

    // Lấy danh sách ảnh đã tải lên
    public function getImagesUploaded(Request $request): JsonResponse
    {
        $perPage = (int)$request->input('per_page', 5);
        $page = (int)$request->input('page', 1);
        $images = $this->imageRepository->getImagesUploaded();
        
        return $this->paginateAndRespond($images, $page, $perPage, 'Get Images Uploaded Error');
    }

    // Lưu trữ hình ảnh tải lên
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
            $this->imageRepository->storeImage($uploadedPaths, Auth::user(), $data);
            
            return response()->json([
                'success' => true,
                'message' => 'Tải lên thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Store Image Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải lên',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
            ], 500);
        }
    }

    // Cập nhật thông tin hình ảnh
    public function update(Request $request, Image $image): JsonResponse
    {
        try {
            $this->imageRepository->updateImage($image, $request->title, $request->prompt);
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Update Image Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
            ], 500);
        }
    }

    // Xóa hình ảnh
    public function destroy(Image $image): JsonResponse
    {
        if (Gate::denies('delete', $image)) {
            return response()->json(['message' => 'Không được phép xóa hình ảnh này'], 403);
        }
        
        try {
            $this->imageRepository->deleteImage($image);
            return response()->json([
                'success' => true,
                'message' => 'Xóa thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete Image Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
            ], 500);
        }
    }
}
