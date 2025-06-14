<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Http\Requests\Image\UpdateImageRequest;
use App\Http\Requests\Image\StoreImageRequest;
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
    public function paginateAndRespond(Request $request, string $typeImage, string $errorType, $userId = null)
    {
        try {
            // Ưu tiên user_id từ request nếu có
            $userId = $request->query('user_id', $userId);
            
            // Log để debug
            \Log::info('paginateAndRespond được gọi với typeImage: ' . $typeImage . ', user_id: ' . $userId);
            
            $result = $this->imageService->paginateAndRespond($request, $typeImage, $errorType, $userId);
            
            // Kiểm tra xem result có phải là JSON resource không
            if ($result instanceof \Illuminate\Http\Resources\Json\JsonResource) {
                return $result;
            }
            
            // Đảm bảo response có cấu trúc nhất quán
            return response()->json([
                'success' => true,
                'data' => $result['data'] ?? [],
                'pagination' => $result['pagination'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu ảnh',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getImagesLiked(Request $request)
    {
        // Lấy user_id từ request query
        $userId = $request->query('user_id');
        
        // Log để debug
        \Log::info('getImagesLiked được gọi với user_id: ' . $userId);
        
        return $this->paginateAndRespond($request, 'liked', 'Get Images Liked Error', $userId);
    }
    public function getImagesUploaded(Request $request)
    {
        // Ưu tiên id từ path, nếu không có thì lấy từ query
        $userId = $request->query('user_id');
        
        // Log để debug
        \Log::info('getImagesUploaded được gọi với user_id: ' . $userId);
        
        return $this->paginateAndRespond($request, 'uploaded', 'Get Images Uploaded Error', $userId);
    }
    
    /**
     * Kiểm tra nếu có hình ảnh mới cho user
     */
    public function checkNewImages(Request $request)
    {
        try {
            $userId = $request->query('user_id');
            $hasNewData = $this->imageService->checkForNewImages($userId);
            
            return response()->json([
                'success' => true,
                'has_new_data' => $hasNewData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể kiểm tra dữ liệu mới',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function store(StoreImageRequest $request, $featureId)
    {
        try {
            // Kiểm tra xem có file được gửi lên không
            if (!$request->hasFile('images')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng tải lên ít nhất một ảnh.'
                ], 422);
            }
            
            // Lấy dữ liệu đã validate và gán files vào
            $data = $request->validated();
            
            $result = $this->imageService->storeImage($data, $featureId);
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
    public function update(UpdateImageRequest $request, Image $image)
    {
        try {
            $result = $this->imageService->updateImage($request->validated(), $image);
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