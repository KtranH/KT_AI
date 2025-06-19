<?php

namespace App\Interfaces;

use App\Models\ImageJob;
use Illuminate\Database\Eloquent\Collection;

interface ImageJobRepositoryInterface
{
    /**
     * Tạo một tiến trình tạo ảnh mới
     */
    public function create(array $data): ImageJob;

    /**
     * Tìm tiến trình theo ID và user ID
     */
    public function findByIdAndUserId(int $jobId, int $userId): ?ImageJob;

    /**
     * Đếm số tiến trình đang hoạt động của user
     */
    public function countActiveJobsByUser(int $userId): int;

    /**
     * Lấy danh sách tiến trình đang hoạt động của user
     */
    public function getActiveJobsByUser(int $userId): Collection;

    /**
     * Lấy danh sách tiến trình đã hoàn thành của user
     */
    public function getCompletedJobsByUser(int $userId): Collection;

    /**
     * Lấy danh sách tiến trình thất bại của user
     */
    public function getFailedJobsByUser(int $userId): Collection;

    /**
     * Cập nhật tiến trình
     */
    public function update(ImageJob $job, array $data): bool;

    /**
     * Tìm tiến trình có thể hủy được
     */
    public function findCancellableJob(int $jobId, int $userId): ?ImageJob;

    /**
     * Tìm tiến trình thất bại để retry
     */
    public function findFailedJob(int $jobId, int $userId): ?ImageJob;

    /**
     * Cập nhật trạng thái tiến trình
     */
    public function updateStatus(ImageJob $job, string $status, ?string $errorMessage = null): bool;

    /**
     * Cập nhật tiến độ tiến trình
     */
    public function updateProgress(ImageJob $job, int $progress): bool;

    /**
     * Cập nhật kết quả ảnh
     */
    public function updateResult(ImageJob $job, string $resultImage): bool;
} 