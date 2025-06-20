<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TurnstileController extends Controller
{
    /**
     * Trả về cấu hình Turnstile cho client
     * Chỉ cung cấp siteKey, giữ secretKey ở phía server
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfig()
    {
        $siteKey = env('TURNSTILE_SITE_KEY');
        return response()->json([
            'siteKey' => $siteKey
        ]);
    }
} 