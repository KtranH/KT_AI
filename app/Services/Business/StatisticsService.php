<?php

declare(strict_types=1);

namespace App\Services\Business;

use App\Interfaces\StatisticsRepositoryInterface;
use App\Models\User;
use App\Services\BaseService;
use Illuminate\Http\JsonResponse;

class StatisticsService extends BaseService
{
    protected StatisticsRepositoryInterface $statisticsRepository;
    
    public function __construct(StatisticsRepositoryInterface $statisticsRepository)
    {
        $this->statisticsRepository = $statisticsRepository;
    }

    /**
     * Lấy thống kê tổng quan của hệ thống
     * 
     * @return array
     */
    public function getSystemStatistics(): array
    {
        return $this->statisticsRepository->getSystemOverview();
    }

    /**
     * Lấy thống kê chi tiết cho người dùng
     * 
     * @param User $user
     * @return array
     */
    public function getUserStatistics(User $user): array
    {
        return [
            'overview' => $this->statisticsRepository->getUserOverview($user),
            'monthlyStats' => $this->statisticsRepository->getMonthlyStats($user),
            'weeklyStats' => $this->statisticsRepository->getWeeklyStats($user),
            'topFeatures' => $this->statisticsRepository->getTopFeatures($user),
            'hourlyActivity' => $this->statisticsRepository->getHourlyActivity($user)
        ];
    }

    /**
     * Lấy thống kê theo khoảng thời gian tùy chỉnh
     * 
     * @param User $user
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getCustomDateRangeStatistics(User $user, string $startDate, string $endDate): array
    {
        // Có thể mở rộng thêm logic cho custom date range
        return $this->getUserStatistics($user);
    }

    /**
     * Tính toán hiệu suất người dùng
     * 
     * @param User $user
     * @return array
     */
    public function calculateUserPerformance(User $user): array
    {
        $overview = $this->statisticsRepository->getUserOverview($user);
        
        // Tính hiệu suất tạo ảnh
        $totalImages = $overview['totalImages'];
        $remainingCredits = $overview['remainingCredits'];
        $usedCredits = 100 - $remainingCredits;
        $performanceScore = $usedCredits > 0 ? min(100, round(($totalImages / $usedCredits) * 100)) : 0;

        // Tính mức độ tương tác
        $totalLikes = $overview['totalLikes'];
        $totalComments = $overview['totalComments'];
        $engagementScore = $totalImages > 0 ? min(100, round((($totalLikes + $totalComments) / $totalImages) * 10)) : 0;

        return [
            'performanceScore' => $performanceScore,
            'engagementScore' => $engagementScore,
            'totalImages' => $totalImages,
            'totalLikes' => $totalLikes,
            'totalComments' => $totalComments,
            'remainingCredits' => $remainingCredits
        ];
    }
} 