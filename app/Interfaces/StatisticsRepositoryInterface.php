<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Support\Collection;

interface StatisticsRepositoryInterface
{
    /**
     * Lấy thống kê tổng quan cho người dùng
     * 
     * @param User $user
     * @return array
     */
    public function getUserOverview(User $user): array;

    /**
     * Lấy thống kê theo tháng cho người dùng
     * 
     * @param User $user
     * @param int $months Số tháng muốn lấy (mặc định 6)
     * @return array
     */
    public function getMonthlyStats(User $user, int $months = 6): array;

    /**
     * Lấy thống kê theo tuần cho người dùng
     * 
     * @param User $user
     * @param int $weeks Số tuần muốn lấy (mặc định 4)
     * @return array
     */
    public function getWeeklyStats(User $user, int $weeks = 4): array;

    /**
     * Lấy top tính năng AI được sử dụng
     * 
     * @param User $user
     * @param int $limit Số lượng kết quả (mặc định 5)
     * @return Collection
     */
    public function getTopFeatures(User $user, int $limit = 5): Collection;

    /**
     * Lấy thống kê hoạt động theo giờ trong ngày
     * 
     * @param User $user
     * @return array
     */
    public function getHourlyActivity(User $user): array;

    /**
     * Lấy thống kê tổng quan của hệ thống
     * 
     * @return array
     */
    public function getSystemOverview(): array;
} 