<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\System;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Services\Business\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatisticsController extends BaseV1Controller
{
    protected StatisticsService $statisticsService;
    
    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    /**
     * Lấy dữ liệu thống kê tổng quan của hệ thống
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $data = $this->statisticsService->getSystemStatistics();
            return $this->successResponseV1($data, 'Lấy dữ liệu thống kê thành công');
        } catch (\Exception $e) {
            return $this->errorResponseV1('Lỗi khi lấy dữ liệu thống kê: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Lấy dữ liệu thống kê cho người dùng cụ thể
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserStatistics(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return $this->errorResponseV1('Người dùng chưa đăng nhập', 401);
        }

        try {
            $data = $this->statisticsService->getUserStatistics($user);

            return $this->successResponseV1($data, 'Lấy dữ liệu thống kê người dùng thành công');

        } catch (\Exception $e) {
            return $this->errorResponseV1('Lỗi khi lấy dữ liệu thống kê: ' . $e->getMessage(), 500);
        }
    }
} 