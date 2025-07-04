<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\System;

use App\Http\Controllers\V1\BaseV1Controller;
use Illuminate\Http\JsonResponse;

class TurnstileController extends BaseV1Controller
{
    /**
     * Lấy cấu hình Turnstile cho client
     * Chỉ cung cấp siteKey, giữ secretKey ở phía server
     *
     * @return JsonResponse
     */
    public function getConfig(): JsonResponse
    {
        $siteKey = env('TURNSTILE_SITE_KEY');
        
        return $this->successResponseV1([
            'siteKey' => $siteKey
        ], 'Lấy cấu hình Turnstile thành công');
    }
} 