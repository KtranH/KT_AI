<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\LikeResource;
use App\Services\LikeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class LikeController extends Controller
{
    protected $likeService;
    public function __construct(LikeService $likeService) {
        $this->likeService = $likeService;
    }
    public function checkLiked($id)
    {
        try {
            $isLiked = $this->likeService->checkLiked($id);
            return response()->json([
                'success' => true,
                'data' => $isLiked,
            ]);
        } catch (\Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu like',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getLikes($id)
    {
        try {
            $likes = $this->likeService->getLikes($id);
            return response()->json([
                'success' => true,
                'like' => LikeResource::collection($likes),
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
    
    public function likePost($id)
    {
        try {
            $result = $this->likeService->likePost($id);
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã thích bài viết thành công'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể like'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể like',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
            ], 500);
        }
    }
    
    public function unlikePost($id)
    {
        try {
            $result = $this->likeService->unlikePost($id);
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã bỏ thích thành công'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể bỏ thích'
                ], 500);
            }
        } catch (\Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Không thể bỏ thích bài viết',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
            ], 500);
        }
    }
}
