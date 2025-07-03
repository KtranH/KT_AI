<?php

namespace App\Interfaces;

use App\Models\ImageJob;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

interface ImageJobRepositoryInterface
{
    /**
     * Tạo một tiến trình tạo ảnh mới
     * @param array $data Dữ liệu của tiến trình
     * @return ImageJob Tiến trình tạo ảnh
     */
    public function create(array $data): ImageJob;

    /**
     * Tìm tiến trình theo ID và user ID
     * @param int $jobId ID của tiến trình
     * @param int $userId ID của user
     * @return ?ImageJob Tiến trình tạo ảnh
     */
    public function findByIdAndUserId(int $jobId, int $userId): ?ImageJob;

    /**
     * Đếm số tiến trình đang hoạt động của user
     * @param int $userId ID của user
     * @return int Số tiến trình đang hoạt động
     */
    public function countActiveJobsByUser(int $userId): int;

    /**
     * Lấy danh sách tiến trình đang hoạt động của user
     * @param int $userId ID của user
     * @return Collection Danh sách tiến trình đang hoạt động
     */
    public function getActiveJobsByUser(int $userId): Collection;

    /**
     * Lấy danh sách tiến trình đã hoàn thành của user
     * @param int $userId ID của user
     * @return Builder Danh sách tiến trình đã hoàn thành
     */
    public function getCompletedJobsByUser(int $userId): Builder;

    /**
     * Lấy danh sách tiến trình thất bại của user
     * @param int $userId ID của user
     * @return Collection Danh sách tiến trình thất bại
     */
    public function getFailedJobsByUser(int $userId): Collection;

    /**
     * Cập nhật tiến trình
     * @param ImageJob $job Tiến trình tạo ảnh
     * @param array $data Dữ liệu của tiến trình
     * @return bool Kết quả cập nhật
     */
    public function update(ImageJob $job, array $data): bool;

    /**
     * Tìm tiến trình có thể hủy được
     * @param int $jobId ID của tiến trình
     * @param int $userId ID của user
     * @return ?ImageJob Tiến trình tạo ảnh
     */
    public function findCancellableJob(int $jobId, int $userId): ?ImageJob;

    /**
     * Tìm tiến trình thất bại để retry
     * @param int $jobId ID của tiến trình
     * @param int $userId ID của user
     * @return ?ImageJob Tiến trình tạo ảnh
     */
    public function findFailedJob(int $jobId, int $userId): ?ImageJob;

    /**
     * Cập nhật trạng thái tiến trình
     * @param ImageJob $job Tiến trình tạo ảnh
     * @param string $status Trạng thái của tiến trình
     * @param ?string $errorMessage Lỗi của tiến trình
     * @return bool Kết quả cập nhật
     */
    public function updateStatus(ImageJob $job, string $status, ?string $errorMessage = null): bool;

    /**
     * Cập nhật tiến độ tiến trình
     * @param ImageJob $job Tiến trình tạo ảnh
     * @param int $progress Tiến độ của tiến trình
     * @return bool Kết quả cập nhật
     */
    public function updateProgress(ImageJob $job, int $progress): bool;

    /**
     * Cập nhật kết quả ảnh
     * @param ImageJob $job Tiến trình tạo ảnh
     * @param string $resultImage Kết quả của tiến trình
     * @return bool Kết quả cập nhật
     */
    public function updateResult(ImageJob $job, string $resultImage): bool;
} 