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
     */
    public function create(array $data): ImageJob
    {
        return ImageJob::create($data);
    }

    /**
     * Tìm tiến trình theo ID và user ID
     */
    public function findByIdAndUserId(int $jobId, int $userId): ?ImageJob
    {
        return ImageJob::where('id', $jobId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Đếm số tiến trình đang hoạt động của user
     */
    public function countActiveJobsByUser(int $userId): int
    {
        return ImageJob::countActiveJobsByUser($userId);
    }

    /**
     * Lấy danh sách tiến trình đang hoạt động của user
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
     */
    public function update(ImageJob $job, array $data): bool
    {
        return $job->update($data);
    }

    /**
     * Tìm tiến trình có thể hủy được
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
     */
    public function updateProgress(ImageJob $job, int $progress): bool
    {
        return $job->update(['progress' => $progress]);
    }

    /**
     * Cập nhật kết quả ảnh
     */
    public function updateResult(ImageJob $job, string $resultImage): bool
    {
        return $job->update(['result_image' => $resultImage]);
    }

    // Legacy methods để tương thích ngược
    public function check5Jobs($userId)
    {
        return ImageJob::where('user_id', $userId)->where('status', '!=', 'done')->count() >= 5;
    }

    public function createImageJob($userId, $prompt)
    {
        return ImageJob::create([
            'user_id' => $userId,
            'prompt' => $prompt,
            'status' => 'pending',
        ]);
    }

    public function updateImageJob($id, $status)
    {
        return ImageJob::where('id', $id)->update([
            'status' => $status,
        ]);
    }

    public function updateResultPathImageJob($id, $resultPath)
    {
        return ImageJob::where('id', $id)->update([
            'result_path' => $resultPath,
        ]);
    }

    public function updateFailedImageJob($id, $errorMessage)
    {
        return ImageJob::where('id', $id)->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function getImageJobs()
    {
        return ImageJob::all();
    }
}
