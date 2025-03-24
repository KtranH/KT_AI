<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Interaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImageController extends Controller
{
    //
    public function getImages($id)
    {
        try {
            $information = Image::where('id', $id)->first();
            if (!$information) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy ảnh với ID: ' . $id
                ], 404, ['Content-Type' => 'application/json']);
            }
            
            $imageUrls = $information ? json_decode($information->image_url, true) : [];
            return response()->json([
                'success' => true,
                'images' => $imageUrls,
                'data' => $information,
                'user' => $information->user
            ], 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            Log::error('Get Images Error: ' . $e->getMessage(), [
                'image_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu ảnh',
                'error' => $e->getMessage()
            ], 500, ['Content-Type' => 'application/json']);
        }
    }
    
    public function getImagesCreatedByUser()
    {
        try {
            $images = Image::where('user_id', Auth::user()->id)->get();
            return response()->json([
                'success' => true,
                'data' => $images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_url' => json_decode($image->image_url, true),
                        'prompt' => $image->prompt,
                        'sum_like' => $image->sum_like,
                        'sum_comment' => $image->sum_comment,
                        'created_at' => $image->created_at,
                        'updated_at' => $image->updated_at,
                    ];
                }),
            ], 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            Log::error('Get Images Created By User Error: ' . $e->getMessage(), [
                'user_id' => Auth::user()->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu ảnh',
                'error' => $e->getMessage()
            ], 500, ['Content-Type' => 'application/json']);
        }
    }
    
    public function checkLiked($id)
    {
        try {
            $likes = Interaction::where('image_id', $id)
                ->where('user_id', Auth::user()->id)
                ->where('type_interaction', 'like')
                ->first();
                
            return response()->json([
                'success' => true,
                'data' => $likes ? true : false,
            ], 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            Log::error('Check Liked Error: ' . $e->getMessage(), [
                'image_id' => $id,
                'user_id' => Auth::user()->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu like',
                'error' => $e->getMessage()
            ], 500, ['Content-Type' => 'application/json']);
        }
    }
    
    public function getLikes($id)
    {
        try {
            $likes = Interaction::where('image_id', $id)
                ->where('type_interaction', 'like')
                ->take(3)
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $likes->map(function ($like) {
                    return [
                        'id' => $like->id,
                        'user_id' => $like->user_id,
                        'user_name' => $like->user->name,
                    ];
                }),
            ], 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            Log::error('Get Likes Error: ' . $e->getMessage(), [
                'image_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu like',
                'error' => $e->getMessage()
            ], 500, ['Content-Type' => 'application/json']);
        }
    }
    
    public function likePost($id)
    {
        try {
            // Kiểm tra xem ảnh có tồn tại không
            $image = Image::find($id);
            if (!$image) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy ảnh với ID: ' . $id
                ], 404, ['Content-Type' => 'application/json']);
            }
            
            // Kiểm tra xem tương tác đã tồn tại chưa
            $existingInteraction = Interaction::where('image_id', $id)
                ->where('user_id', Auth::user()->id)
                ->where('type_interaction', 'like')
                ->first();
                
            if ($existingInteraction) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bạn đã thích bài viết này rồi'
                ], 200, ['Content-Type' => 'application/json']);
            }
            
            // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::beginTransaction();
            
            try {
                // Tạo mới tương tác
                $interaction = new Interaction();
                $interaction->image_id = $id;
                $interaction->user_id = Auth::user()->id;
                $interaction->status_interaction = 'active';
                $interaction->type_interaction = 'like';
                $interaction->save();
                
                // Cập nhật số lượt thích cho ảnh
                $image->increment('sum_like');
                
                // Cập nhật số lượt thích cho người dùng
                $user = User::find(Auth::user()->id);
                if ($user) {
                    $user->increment('sum_like');
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Đã thích bài viết thành công'
                ], 200, ['Content-Type' => 'application/json']);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Like Post Error: ' . $e->getMessage(), [
                'image_id' => $id,
                'user_id' => Auth::user()->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể like',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
            ], 500, ['Content-Type' => 'application/json']);
        }
    }
    
    public function unlikePost($id)
    {
        try {
            // Kiểm tra xem ảnh có tồn tại không
            $image = Image::find($id);
            if (!$image) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy ảnh với ID: ' . $id
                ], 404, ['Content-Type' => 'application/json']);
            }
            
            // Tìm tương tác
            $interaction = Interaction::where('image_id', $id)
                ->where('user_id', Auth::user()->id)
                ->where('type_interaction', 'like')
                ->first();
                
            if (!$interaction) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bạn chưa thích bài viết này hoặc đã bỏ thích trước đó'
                ], 200, ['Content-Type' => 'application/json']);
            }
            
            // Sử dụng transaction để đảm bảo tính toàn vẹn dữ liệu
            DB::beginTransaction();
            
            try {
                // Xóa tương tác
                $interaction->delete();
                
                // Giảm số lượt thích cho ảnh nếu hiện tại > 0
                if ($image->sum_like > 0) {
                    $image->decrement('sum_like');
                }
                
                // Giảm số lượt thích cho người dùng nếu hiện tại > 0
                $user = User::find(Auth::user()->id);
                if ($user && $user->sum_like > 0) {
                    $user->decrement('sum_like');
                }
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Đã bỏ thích thành công'
                ], 200, ['Content-Type' => 'application/json']);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Unlike Post Error: ' . $e->getMessage(), [
                'image_id' => $id,
                'user_id' => Auth::user()->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Không thể bỏ thích bài viết',
                'error' => config('app.debug') ? $e->getMessage() : 'Lỗi hệ thống'
            ], 500, ['Content-Type' => 'application/json']);
        }
    }
}
