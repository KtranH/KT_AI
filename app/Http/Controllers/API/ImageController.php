<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    //
    public function getImages($id)
    {
        try {
            $information = Image::where('id', $id)->select('image_url')->first();
            $imageUrls = $information ? json_decode($information->image_url, true) : [];
            return response()->json([
                'success' => true,
                'data' => $imageUrls
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
