<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class VerifyTurnstile
{
    /**
     * Xử lý yêu cầu đến
     * @param Request $request Yêu cầu
     * @param Closure $next Hàm tiếp theo
     * @return mixed Phản hồi
     */
    public function handle(Request $request, Closure $next)
    {
        // Kiểm tra xem token Turnstile có tồn tại không
        if (!$request->has('cf-turnstile-response')) {
            throw ValidationException::withMessages([
                'captcha' => ['Vui lòng xác nhận bạn không phải robot.'],
            ]);
        }

        // Gửi request đến Cloudflare để xác thực token
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => env('TURNSTILE_SECRET_KEY'),
            'response' => $request->input('cf-turnstile-response'),
            'remoteip' => $request->ip(),
        ]);

        // Kiểm tra kết quả từ Cloudflare
        if (!$response->json('success')) {
            throw ValidationException::withMessages([
                'captcha' => ['Xác thực không thành công. Vui lòng thử lại.'],
            ]);
        }

        return $next($request);
    }
} 