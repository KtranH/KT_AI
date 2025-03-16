<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
