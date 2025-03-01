<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class AuthenticateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra xác thực
        if (!Auth::check()) {
            // Kiểm tra token từ API request
            if ($request->bearerToken()) {
                $token = PersonalAccessToken::findToken($request->bearerToken());
                
                if (!$token) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Token không hợp lệ hoặc đã hết hạn.',
                            'status' => 401
                        ], 401);
                    }
                    return redirect()->route('login');
                }
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'status' => 401
                ], 401);
            }
            return redirect()->route('login');
        }

        return $next($request);
    }
}
