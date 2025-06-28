<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiMiddleware
{
    /**
     * Xử lý yêu cầu đến
     * @param Request $request Yêu cầu
     * @param Closure $next Hàm tiếp theo
     * @return Response Phản hồi
     */
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        
        $response = $next($request);
        
        // Thêm các headers cần thiết cho API
        if ($response instanceof Response) {
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        }
        
        return $response;
    }
}
