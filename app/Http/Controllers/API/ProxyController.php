<?php

// Class này để proxy hình ảnh từ R2.dev và tải xuống ảnh trực tiếp từ R2Storage
// Chưa hoàn thiện và sử dụng

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\External\R2StorageService;
use Illuminate\Http\JsonResponse;

class ProxyController extends Controller
{
    /**
     * Proxy yêu cầu đến Cloudflare R2 và truyền phản hồi về
     * 
     * @param Request $request
     * @return mixed
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
    
    /**
     * Tải xuống ảnh trực tiếp từ R2 
     * Hỗ trợ tải xuống đúng định dạng không bị lỗi ở Windows
     * 
     * @param Request $request
     * @return mixed
     */
    public function downloadR2Image(Request $request)
    {
        try {
            $url = $request->input('url');
            $filename = $request->input('filename', 'image');
            
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
                // Log thông tin để debug
                Log::info("Download R2 Image - URL: {$url}, Content-Type: " . ($response->header('Content-Type') ?? 'Unknown'));
                
                // Xác định content type và extension phù hợp
                $originalContentType = $response->header('Content-Type');
                $extension = 'png'; // Default
                
                // Xác định extension dựa vào content type
                if ($originalContentType) {
                    if (strpos($originalContentType, 'jpeg') !== false || strpos($originalContentType, 'jpg') !== false) {
                        $extension = 'jpg';
                        $contentType = 'image/jpeg';
                    } elseif (strpos($originalContentType, 'png') !== false) {
                        $extension = 'png';
                        $contentType = 'image/png';
                    } elseif (strpos($originalContentType, 'webp') !== false) {
                        $extension = 'webp';
                        $contentType = 'image/webp';
                    } elseif (strpos($originalContentType, 'gif') !== false) {
                        $extension = 'gif';
                        $contentType = 'image/gif';
                    } else {
                        $contentType = 'image/png';
                    }
                } else {
                    // Nếu không có content type, thử từ URL
                    $urlPath = parse_url($url, PHP_URL_PATH);
                    $pathInfo = pathinfo($urlPath);
                    if (isset($pathInfo['extension'])) {
                        $urlExt = strtolower($pathInfo['extension']);
                        if (in_array($urlExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            $extension = $urlExt == 'jpeg' ? 'jpg' : $urlExt;
                            $contentType = 'image/' . ($extension == 'jpg' ? 'jpeg' : $extension);
                        }
                    }
                }
                
                // Thêm log để debug
                Log::info("Download R2 Image - Extension: {$extension}, Content-Type: {$contentType}");
                
                // Trả về nội dung hình ảnh với header phù hợp
                return response($response->body())
                    ->header('Content-Type', $contentType)
                    ->header('Content-Length', $response->header('Content-Length'))
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '.' . $extension . '"')
                    ->header('Cache-Control', 'no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET')
                    ->header('Expires', '0');
            }
            
            // Trả về lỗi nếu yêu cầu không thành công
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải hình ảnh từ máy chủ từ xa',
                'status' => $response->status()
            ], $response->status());
            
        } catch (\Exception $e) {
            Log::error("Lỗi download R2: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tải hình ảnh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tải trực tiếp file từ R2Storage xuống máy người dùng
     * Sử dụng hàm download của R2StorageService
     * 
     * @param Request $request
     * @param R2StorageService $r2Service
     * @return mixed
     */
    public function downloadFromR2Storage(Request $request, R2StorageService $r2Service)  
    {
        try {
            $url = $request->input('url');
            $filename = $request->input('filename', 'image');
            
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
            
            // Xác định extension từ URL
            $urlPath = parse_url($url, PHP_URL_PATH);
            $pathInfo = pathinfo($urlPath);
            $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : 'png';
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }
            
            // Xác định Content-Type dựa vào extension
            $contentTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
            ];
            
            $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
            
            // Log thông tin để debug
            Log::info("Downloading from R2Storage - URL: {$url}, Extension: {$extension}");
            
            // Tải nội dung file từ R2 sử dụng hàm download
            $fileContent = $r2Service->download($url);
            
            // Trả về nội dung file với headers phù hợp để trình duyệt tự động tải xuống
            return response($fileContent)
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '.' . $extension . '"')
                ->header('Content-Length', strlen($fileContent))
                ->header('Cache-Control', 'no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
                
        } catch (\Exception $e) {
            Log::error("Lỗi khi tải file từ R2Storage: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tải file: ' . $e->getMessage()
            ], 500);
        }
    }
} 