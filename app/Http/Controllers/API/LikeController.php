<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Interfaces\LikeRepositoryInterface;


class LikeController extends Controller
{
    public function __construct(private readonly LikeRepositoryInterface $likeRepository) {}
    
    public function checkLiked($id): JsonResponse
    {
        try {
            $isLiked = $this->likeRepository->checkLiked($id);
            
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
            $likes = $this->likeRepository->getLikes($id);
            
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
            $this->likeRepository->likePost($id);
            
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
            $this->likeRepository->unlikePost($id);
            
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
}
