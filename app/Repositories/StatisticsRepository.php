<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\StatisticsRepositoryInterface;
use App\Models\User;
use App\Models\Image;
use App\Models\Comment;
use App\Models\Like;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsRepository implements StatisticsRepositoryInterface
{
    /**
     * Lấy thống kê tổng quan cho người dùng
     * 
     * @param User $user
     * @return array
     */
    public function getUserOverview(User $user): array
    {
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

        return [
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
        ];
    }

    /**
     * Lấy thống kê theo tháng cho người dùng
     * 
     * @param User $user
     * @param int $months Số tháng muốn lấy (mặc định 6)
     * @return array
     */
    public function getMonthlyStats(User $user, int $months = 6): array
    {
        $monthlyStats = [];
        $monthlyLabels = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
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

        return [
            'labels' => $monthlyLabels,
            'data' => $monthlyStats
        ];
    }

    /**
     * Lấy thống kê theo tuần cho người dùng
     * 
     * @param User $user
     * @param int $weeks Số tuần muốn lấy (mặc định 4)
     * @return array
     */
    public function getWeeklyStats(User $user, int $weeks = 4): array
    {
        $weeklyStats = [];
        $weeklyLabels = [];
        
        for ($i = $weeks - 1; $i >= 0; $i--) {
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

        return [
            'labels' => $weeklyLabels,
            'data' => $weeklyStats
        ];
    }

    /**
     * Lấy top tính năng AI được sử dụng
     * 
     * @param User $user
     * @param int $limit Số lượng kết quả (mặc định 5)
     * @return Collection
     */
    public function getTopFeatures(User $user, int $limit = 5): Collection
    {
        return Image::where('user_id', $user->id)
            ->join('ai_features', 'images.feature_id', '=', 'ai_features.id')
            ->select('ai_features.name', 'ai_features.id', DB::raw('count(*) as count'))
            ->groupBy('ai_features.id', 'ai_features.name')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy thống kê hoạt động theo giờ trong ngày
     * 
     * @param User $user
     * @return array
     */
    public function getHourlyActivity(User $user): array
    {
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

        return $hourlyActivity;
    }

    /**
     * Lấy thống kê tổng quan của hệ thống
     * 
     * @return array
     */
    public function getSystemOverview(): array
    {
        $today = Carbon::today();
        
        return [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('status_user', 'active')->count(),
            'newUsersToday' => User::whereDate('created_at', $today)->count(),
            'totalImages' => Image::count(),
            'imagesUploadedToday' => Image::whereDate('created_at', $today)->count(),
            'totalComments' => Comment::count(),
            'commentsToday' => Comment::whereDate('created_at', $today)->count(),
            'likesToday' => Like::whereDate('created_at', $today)->count(),
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
    }
} 