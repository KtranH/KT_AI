<?php

namespace App\Repositories;

use App\Interfaces\ImageJobRepositoryInterface;
use App\Models\ImageJob;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class ImageJobsRepository implements ImageJobRepositoryInterface
{
    /**
     * Tạo một tiến trình tạo ảnh mới
     * @param array $data Dữ liệu của tiến trình
     * @return ImageJob Tiến trình tạo ảnh
     */
    public function create(array $data): ImageJob
    {
        return ImageJob::create($data);
    }

    /**
     * Tìm tiến trình theo ID và user ID
     * @param int $jobId ID của tiến trình
     * @param int $userId ID của user
     * @return ImageJob|null Tiến trình tạo ảnh
     */
    public function findByIdAndUserId(int $jobId, int $userId): ?ImageJob
    {
        return ImageJob::where('id', $jobId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Đếm số tiến trình đang hoạt động của user
     * @param int $userId ID của user
     * @return int Số lượng tiến trình đang hoạt động
     */
    public function countActiveJobsByUser(int $userId): int
    {
        return ImageJob::countActiveJobsByUser($userId);
    }

    /**
     * Lấy danh sách tiến trình đang hoạt động của user
     * @param int $userId ID của user
     * @return Collection Tiến trình tạo ảnh
     */
    public function getActiveJobsByUser(int $userId): Collection
    {
        return ImageJob::where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Lấy danh sách tiến trình đã hoàn thành của user
     * @param int $userId ID của user
     * @return Builder Tiến trình tạo ảnh
     */
    public function getCompletedJobsByUser(int $userId): Builder
    {
        $query = ImageJob::query();
        return $query->where('user_id', $userId)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Lấy danh sách tiến trình thất bại của user
     * @param int $userId ID của user
     * @return Builder Tiến trình tạo ảnh
     */
    public function getFailedJobsByUser(int $userId): Builder
    {
        $query = ImageJob::query();
        return $query->where('user_id', $userId)
            ->where('status', 'failed')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Cập nhật tiến trình
     * @param ImageJob $job Tiến trình tạo ảnh
     * @param array $data Dữ liệu của tiến trình
     * @return bool Kết quả
     */
    public function update(ImageJob $job, array $data): bool
    {
        return $job->update($data);
    }

    /**
     * Tìm tiến trình có thể hủy được
     * @param int $jobId ID của tiến trình
     * @param int $userId ID của user
     * @return ImageJob|null Tiến trình tạo ảnh
     */
    public function findCancellableJob(int $jobId, int $userId): ?ImageJob
    {
        return ImageJob::where('id', $jobId)
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'processing'])
            ->first();
    }

    /**
     * Tìm tiến trình thất bại để retry
     * @param int $jobId ID của tiến trình
     * @param int $userId ID của user
     * @return ImageJob|null Tiến trình tạo ảnh
     */
    public function findFailedJob(int $jobId, int $userId): ?ImageJob
    {
        return ImageJob::where('id', $jobId)
            ->where('user_id', $userId)
            ->where('status', 'failed')
            ->first();
    }

    /**
     * Cập nhật trạng thái tiến trình
     * @param ImageJob $job Tiến trình tạo ảnh
     * @param string $status Trạng thái của tiến trình
     * @param string|null $errorMessage Lỗi tạo ảnh
     * @return bool Kết quả
     */
    public function updateStatus(ImageJob $job, string $status, ?string $errorMessage = null): bool
    {
        $data = ['status' => $status];
        if ($errorMessage !== null) {
            $data['error_message'] = $errorMessage;
        }
        
        return $job->update($data);
    }

    /**
     * Cập nhật tiến độ tiến trình
     * @param ImageJob $job Tiến trình tạo ảnh
     * @param int $progress Tiến độ tạo ảnh
     * @return bool Kết quả
     */
    public function updateProgress(ImageJob $job, int $progress): bool
    {
        return $job->update(['progress' => $progress]);
    }

    /**
     * Cập nhật kết quả ảnh
     * @param ImageJob $job Tiến trình tạo ảnh
     * @param string $resultImage Đường dẫn kết quả ảnh
     * @return bool Kết quả
     */
    public function updateResult(ImageJob $job, string $resultImage): bool
    {
        return $job->update(['result_image' => $resultImage]);
    }

    /**
     * Kiểm tra số lượng tiến trình đang chạy của user
     * @param int $userId ID của user
     * @return bool Kết quả
     */
    public function check5Jobs($userId)
    {
        return ImageJob::where('user_id', $userId)->where('status', '!=', 'done')->count() >= 5;
    }

    /**
     * Tạo tiến trình tạo ảnh
     * @param int $userId ID của user
     * @param string $prompt Prompt tạo ảnh
     * @return ImageJob Tiến trình tạo ảnh
     */
    public function createImageJob($userId, $prompt)
    {
        return ImageJob::create([
            'user_id' => $userId,
            'prompt' => $prompt,
            'status' => 'pending',
        ]);
    }

    /**
     * Cập nhật tiến trình tạo ảnh
     * @param int $id ID của tiến trình
     * @param string $status Trạng thái của tiến trình
     * @return bool Kết quả
     */
    public function updateImageJob($id, $status)
    {
        return ImageJob::where('id', $id)->update([
            'status' => $status,
        ]);
    }

    /**
     * Cập nhật đường dẫn kết quả ảnh
     * @param int $id ID của tiến trình
     * @param string $resultPath Đường dẫn kết quả ảnh
     * @return bool Kết quả
     */
    public function updateResultPathImageJob($id, $resultPath)
    {
        return ImageJob::where('id', $id)->update([
            'result_path' => $resultPath,
        ]);
    }

    /**
     * Cập nhật tiến trình tạo ảnh thất bại
     * @param int $id ID của tiến trình
     * @param string $errorMessage Lỗi tạo ảnh
     * @return bool Kết quả
     */
    public function updateFailedImageJob($id, $errorMessage)
    {
        return ImageJob::where('id', $id)->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Lấy danh sách tiến trình tạo ảnh
     * @return Collection Danh sách tiến trình tạo ảnh
     */
    public function getImageJobs()
    {
        return ImageJob::all();
    }
}
