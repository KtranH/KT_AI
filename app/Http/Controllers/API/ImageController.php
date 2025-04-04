<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\R2StorageService;
use App\Repositories\ImageRepository;

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
    
    public function getImagesCreatedByUser(Request $request): JsonResponse
    {
        try {
            $perPage = (int)$request->input('per_page', 5);
            $page = (int)$request->input('page', 1);
            
            // Lấy tất cả hình ảnh của người dùng
            $images = $this->imageRepository->getImagesCreatedByUser();
            
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
            Log::error('Get Images Created By User Error: ' . $e->getMessage(), [
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
    
            $files = $request->file('images'); // Laravel sẽ nhận mảng file ở đây
        
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
}
