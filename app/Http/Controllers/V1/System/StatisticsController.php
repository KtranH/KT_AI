<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\System;

use App\Http\Controllers\V1\BaseV1Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends BaseV1Controller
{
    /**
     * Lấy dữ liệu thống kê tổng quan của hệ thống
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getStatistics(Request $request): JsonResponse
    {
        // Dữ liệu thống kê mẫu - có thể thay bằng service layer sau
        $data = [
            'totalUsers' => 1500,
            'activeUsers' => 1200,
            'newUsersToday' => 50,
            'totalImages' => 5000,
            'imagesUploadedToday' => 100,
            'totalComments' => 12000,
            'commentsToday' => 300,
            'likesToday' => 1500,
            'chartData' => [
                'labels' => ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                'datasets' => [
                    [
                        'label' => 'Người dùng mới',
                        'backgroundColor' => '#f87979',
                        'data' => [40, 20, 12, 39, 10, 40]
                    ],
                    [
                        'label' => 'Hình ảnh được tải lên',
                        'backgroundColor' => '#36a2eb',
                        'data' => [60, 55, 32, 45, 20, 30]
                    ]
                ]
            ]
        ];

        return $this->successResponseV1($data, 'Lấy dữ liệu thống kê thành công');
    }
} 