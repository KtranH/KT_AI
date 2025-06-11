<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProxyController extends Controller
{
    /**
     * Proxy yêu cầu đến Cloudflare R2 và truyền phản hồi về
     */
    public function proxyR2Image(Request $request)
    {
        try {
            $url = $request->input('url');
            
            // Kiểm tra URL có hợp lệ không
            if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL không hợp lệ'
                ], 400);
            }
            
            // Kiểm tra xem URL có phải là từ R2.dev hay không
            if (!str_contains($url, 'r2.dev')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ hỗ trợ URL từ R2.dev'
                ], 400);
            }
            
            // Thực hiện yêu cầu HTTP đến máy chủ từ xa
            $response = Http::get($url);
            
            // Kiểm tra nếu yêu cầu thành công
            if ($response->successful()) {
                // Trả về nội dung hình ảnh với header phù hợp
                return response($response->body())
                    ->header('Content-Type', $response->header('Content-Type') ?? 'image/png')
                    ->header('Content-Length', $response->header('Content-Length'))
                    ->header('Content-Disposition', 'attachment; filename="' . basename($url) . '"');
            }
            
            // Trả về lỗi nếu yêu cầu không thành công
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải hình ảnh từ máy chủ từ xa',
                'status' => $response->status()
            ], $response->status());
            
        } catch (\Exception $e) {
            Log::error("Lỗi proxy R2: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi proxy hình ảnh: ' . $e->getMessage()
            ], 500);
        }
    }
} 