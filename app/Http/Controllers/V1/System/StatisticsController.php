<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1\System;

use App\Http\Controllers\V1\BaseV1Controller;
use App\Models\Image;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            // Thống kê tổng quan
            $totalImages = Image::where('user_id', $user->id)->count();
            $totalLikes = Like::where('user_id', $user->id)->count();
            $totalComments = Comment::where('user_id', $user->id)->count();
            
            // Thống kê hôm nay
            $today = Carbon::today();
            $imagesToday = Image::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->count();
            $likesToday = Like::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->count();
            $commentsToday = Comment::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->count();

            // Thống kê theo tháng (6 tháng gần nhất)
            $monthlyStats = [];
            $monthlyLabels = [];
            
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $monthLabel = $month->format('M Y');
                $monthlyLabels[] = $monthLabel;
                
                $monthStart = $month->startOfMonth();
                $monthEnd = $month->endOfMonth();
                
                $monthlyStats[] = [
                    'images' => Image::where('user_id', $user->id)
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->count(),
                    'likes' => Like::where('user_id', $user->id)
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->count(),
                    'comments' => Comment::where('user_id', $user->id)
                        ->whereBetween('created_at', [$monthStart, $monthEnd])
                        ->count()
                ];
            }

            // Thống kê theo tuần (4 tuần gần nhất)
            $weeklyStats = [];
            $weeklyLabels = [];
            
            for ($i = 3; $i >= 0; $i--) {
                $week = Carbon::now()->subWeeks($i);
                $weekLabel = 'Tuần ' . $week->weekOfYear;
                $weeklyLabels[] = $weekLabel;
                
                $weekStart = $week->startOfWeek();
                $weekEnd = $week->endOfWeek();
                
                $weeklyStats[] = [
                    'images' => Image::where('user_id', $user->id)
                        ->whereBetween('created_at', [$weekStart, $weekEnd])
                        ->count(),
                    'likes' => Like::where('user_id', $user->id)
                        ->whereBetween('created_at', [$weekStart, $weekEnd])
                        ->count(),
                    'comments' => Comment::where('user_id', $user->id)
                        ->whereBetween('created_at', [$weekStart, $weekEnd])
                        ->count()
                ];
            }

            // Thống kê top tính năng AI được sử dụng
            $topFeatures = Image::where('user_id', $user->id)
                ->join('ai_features', 'images.feature_id', '=', 'ai_features.id')
                ->select('ai_features.name', 'ai_features.id', DB::raw('count(*) as count'))
                ->groupBy('ai_features.id', 'ai_features.name')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get();

            // Thống kê hoạt động theo giờ trong ngày
            $hourlyActivity = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = Carbon::today()->addHours($hour);
                $hourEnd = $hourStart->copy()->addHour();
                
                $hourlyActivity[] = [
                    'hour' => $hour,
                    'images' => Image::where('user_id', $user->id)
                        ->whereBetween('created_at', [$hourStart, $hourEnd])
                        ->count(),
                    'likes' => Like::where('user_id', $user->id)
                        ->whereBetween('created_at', [$hourStart, $hourEnd])
                        ->count(),
                    'comments' => Comment::where('user_id', $user->id)
                        ->whereBetween('created_at', [$hourStart, $hourEnd])
                        ->count()
                ];
            }

            $data = [
                'overview' => [
                    'totalImages' => $totalImages,
                    'totalLikes' => $totalLikes,
                    'totalComments' => $totalComments,
                    'remainingCredits' => $user->remaining_credits ?? 0,
                    'memberSince' => $user->created_at->format('d/m/Y'),
                    'today' => [
                        'images' => $imagesToday,
                        'likes' => $likesToday,
                        'comments' => $commentsToday
                    ]
                ],
                'monthlyStats' => [
                    'labels' => $monthlyLabels,
                    'data' => $monthlyStats
                ],
                'weeklyStats' => [
                    'labels' => $weeklyLabels,
                    'data' => $weeklyStats
                ],
                'topFeatures' => $topFeatures,
                'hourlyActivity' => $hourlyActivity
            ];

            return $this->successResponseV1($data, 'Lấy dữ liệu thống kê người dùng thành công');

        } catch (\Exception $e) {
            return $this->errorResponseV1('Lỗi khi lấy dữ liệu thống kê: ' . $e->getMessage(), 500);
        }
    }
} 