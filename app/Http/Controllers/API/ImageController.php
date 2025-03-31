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
use App\Services\ImageService;

class ImageController extends Controller
{
    public function __construct(private readonly ImageService $imageService, 
                                private readonly R2StorageService $r2StorageService) {}

    public function getImages($id): JsonResponse
    {
        try {
            $result = $this->imageService->getImage($id);
            
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
            $result = $this->imageService->getImagesByFeature($id);
            
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
    
    public function getImagesCreatedByUser(): JsonResponse
    {
        try {
            $images = $this->imageService->getImagesCreatedByUser();
            
            return response()->json([
                'success' => true,
                'data' => ImageResource::collection($images),
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
    
    public function checkLiked($id): JsonResponse
    {
        try {
            $isLiked = $this->imageService->checkLiked($id);
            
            return response()->json([
                'success' => true,
                'data' => $isLiked,
            ]);
        } catch (\Exception $e) {
            Log::error('Check Liked Error: ' . $e->getMessage(), [
                'image_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu like',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getLikes($id): JsonResponse
    {
        try {
            $likes = $this->imageService->getLikes($id);
            
            return response()->json([
                'success' => true,
                'data' => $likes->map(function ($like) {
                    return [
                        'id' => $like->id,
                        'user_id' => $like->user_id,
                        'user_name' => $like->user->name,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('Get Likes Error: ' . $e->getMessage(), [
                'image_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu like',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function likePost($id): JsonResponse
    {
        try {
            $this->imageService->likePost($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã thích bài viết thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Like Post Error: ' . $e->getMessage(), [
                'image_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể like',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
            ], 500);
        }
    }
    
    public function unlikePost($id): JsonResponse
    {
        try {
            $this->imageService->unlikePost($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã bỏ thích thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Unlike Post Error: ' . $e->getMessage(), [
                'image_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể bỏ thích bài viết',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
            ], 500);
        }
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
            $this->imageService->storeImage($uploadedPaths, Auth::user(), $data);
            
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
