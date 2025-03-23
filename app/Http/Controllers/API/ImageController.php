<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Interaction;
use App\Models\User;

class ImageController extends Controller
{
    //
    public function getImages($id)
    {
        try {
            $information = Image::where('id', $id)->first();
            $imageUrls = $information ? json_decode($information->image_url, true) : [];
            return response()->json([
                'success' => true,
                'images' => $imageUrls,
                'data' => $information,
                'user' => $information->user
            ], 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
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
            $likes = Interaction::where('image_id', $id)->where('user_id', Auth::user()->id)->where('type_interaction', 'like')->first();
            return response()->json([
                'success' => true,
                'data' => $likes ? true : false,
            ], 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
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
            $likes = Interaction::where('image_id', $id)->take(3)->get();
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
                $interaction = new Interaction();
                $interaction->image_id = $id;
                $interaction->user_id = Auth::user()->id;
                $interaction->status_interaction = 'active';
                $interaction->type_interaction = 'like';
                $interaction->save();
                Image::find($id)->increment('sum_like');
                User::find(Auth::user()->id)->increment('sum_like');
                return response()->json([
                    'success' => true,
                ], 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể like',
                'error' => $e->getMessage()
            ], 500, ['Content-Type' => 'application/json']);
        }
    }
    public function unlikePost($id)
    {
        try {
            $interaction = Interaction::where('image_id', $id)
                ->where('user_id', Auth::user()->id)
                ->where('type_interaction', 'like')
                ->first();
                
            if ($interaction) {
                $interaction->delete();
                Image::find($id)->decrement('sum_like');
                User::find(Auth::user()->id)->decrement('sum_like');
                return response()->json([
                    'success' => true,
                    'message' => 'Đã bỏ thích thành công'
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Bạn chưa thích bài viết này hoặc đã bỏ thích trước đó',
                ], 200);
            }
        } catch (\Exception $e) {
            \Log::error('Unlike Post Error: ' . $e->getMessage(), [
                'image_id' => $id,
                'user_id' => Auth::user()->id,
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
