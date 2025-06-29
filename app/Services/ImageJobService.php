<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\ImageJobRepositoryInterface;
use App\Jobs\CheckImageJobStatus;
use App\Models\ImageJob;
use App\Models\User;
use App\Services\ComfyUIService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ImageJobService extends BaseService
{
    protected ImageJobRepositoryInterface $imageJobRepository;
    protected ComfyUIService $comfyUIService;

    public function __construct(
        ImageJobRepositoryInterface $imageJobRepository,
        ComfyUIService $comfyUIService,
    ) {
        $this->imageJobRepository = $imageJobRepository;
        $this->comfyUIService = $comfyUIService;
    }

    /**
     * Tạo tiến trình tạo ảnh mới
     */
    public function createImageJob(array $data, User $user): array
    {
        return $this->executeInTransactionSafely(function() use ($data, $user) {
        // Kiểm tra giới hạn tiến trình
        $activeJobsCount = $this->imageJobRepository->countActiveJobsByUser($user->id);
        if ($activeJobsCount >= 5) {
            throw ValidationException::withMessages([
                'limit' => ['Bạn đã đạt đến giới hạn 5 tiến trình đang hoạt động. Vui lòng đợi cho đến khi một số tiến trình hoàn thành.']
            ]);
        }

        // Xử lý upload ảnh
        $mainImagePath = $this->handleImageUpload($data['main_image'] ?? null);
        $secondaryImagePath = $this->handleImageUpload($data['secondary_image'] ?? null);

        // Tạo tiến trình
        $jobData = [
            'user_id' => $user->id,
            'feature_id' => $data['feature_id'],
            'prompt' => $data['prompt'],
            'width' => $data['width'],
            'height' => $data['height'],
            'seed' => $data['seed'],
            'style' => $data['style'] ?? null,
            'main_image' => $mainImagePath,
            'secondary_image' => $secondaryImagePath,
            'status' => 'pending',
            'progress' => 0,
        ];

        $imageJob = $this->imageJobRepository->create($jobData);

        // Gửi yêu cầu đến ComfyUI
        $promptId = $this->comfyUIService->createImage($imageJob);

        if (!$promptId) {
            throw new \Exception('Lỗi khi tạo ảnh với ComfyUI: ' . ($imageJob->error_message ?? 'Lỗi không xác định'));
        }

        // Đưa vào queue để kiểm tra tiến trình
        dispatch(new CheckImageJobStatus($imageJob->id, $promptId))
            ->onQueue('image-processing')
            ->delay(now()->addSeconds(15));
            
        return [
            'job_id' => $imageJob->id,
                'status' => $imageJob->status,
            ];
        }, "Creating image job for user ID: {$user->id}");
    }

    /**
     * Lấy danh sách tiến trình đang hoạt động
     */
    public function getActiveJobs(User $user): array
    {
        return $this->executeWithExceptionHandling(function() use ($user) {
            $activeJobs = $this->imageJobRepository->getActiveJobsByUser($user->id);

        return [
            'active_jobs' => $activeJobs,
            'count' => $activeJobs->count(),
            ];
        }, "Getting active jobs for user ID: {$user->id}");
    }

    /**
     * Lấy danh sách tiến trình đã hoàn thành
     */
    public function getCompletedJobs(User $user): array
    {
        return $this->executeWithExceptionHandling(function() use ($user) {
            $completedJobs = $this->imageJobRepository->getCompletedJobsByUser($user->id);

        return [
            'completed_jobs' => $completedJobs,
            'count' => $completedJobs->count(),
            ];
        }, "Getting completed jobs for user ID: {$user->id}");
    }

    /**
     * Lấy danh sách tiến trình thất bại
     */
    public function getFailedJobs(User $user): array
    {
        return $this->executeWithExceptionHandling(function() use ($user) {
            $failedJobs = $this->imageJobRepository->getFailedJobsByUser($user->id);

        return [
            'failed_jobs' => $failedJobs,
            'count' => $failedJobs->count(),
            ];
        }, "Getting failed jobs for user ID: {$user->id}");
    }

    /**
     * Kiểm tra trạng thái tiến trình
     */
    public function checkJobStatus(int $jobId, User $user): array
    {
        return $this->executeWithExceptionHandling(function() use ($jobId, $user) {
            $job = $this->imageJobRepository->findByIdAndUserId($jobId, $user->id);

        if (!$job) {
            throw new \Exception('Không tìm thấy tiến trình');
        }

        // Nếu tiến trình đang xử lý và có ID prompt, tạo queue job để kiểm tra
        if (($job->status === 'processing' || $job->status === 'pending') && $job->comfy_prompt_id) {
            dispatch(new CheckImageJobStatus($job->id, $job->comfy_prompt_id))
                ->onQueue('image-processing-low')
                ->delay(now()->addSeconds(5));

            Log::info("Đã thêm job {$job->id} vào queue để kiểm tra trạng thái");
        }

        return [
            'job' => $job
            ];
        }, "Checking job status for job ID: {$jobId} and user ID: {$user->id}");
    }

    /**
     * Hủy tiến trình
     */
    public function cancelJob(int $jobId, User $user): array
    {
        return $this->executeInTransactionSafely(function() use ($jobId, $user) {
            $job = $this->imageJobRepository->findCancellableJob($jobId, $user->id);

        if (!$job) {
            throw new \Exception('Không tìm thấy tiến trình hoặc tiến trình không thể hủy');
        }

        // Cập nhật trạng thái
        $this->imageJobRepository->updateStatus($job, 'failed', 'Tiến trình đã bị hủy bởi người dùng');

        // Xóa các ảnh đã tải lên
        $this->cleanupJobImages($job);

        return [
            'job_id' => $jobId,
            'status' => 'cancelled'
            ];
        }, "Cancelling job ID: {$jobId} for user ID: {$user->id}");
    }

    /**
     * Thử lại tiến trình thất bại
     */
    public function retryJob(int $jobId, User $user): array
    {
        return $this->executeInTransactionSafely(function() use ($jobId, $user) {
            $job = $this->imageJobRepository->findFailedJob($jobId, $user->id);

        if (!$job) {
            throw new \Exception('Không tìm thấy tiến trình thất bại');
        }

        // Kiểm tra giới hạn tiến trình
        $activeJobsCount = $this->imageJobRepository->countActiveJobsByUser($user->id);
        if ($activeJobsCount >= 5) {
            throw ValidationException::withMessages([
                'limit' => ['Bạn đã đạt đến giới hạn 5 tiến trình đang hoạt động. Vui lòng đợi cho đến khi một số tiến trình hoàn thành.']
            ]);
        }

        // Reset trạng thái
        $this->imageJobRepository->update($job, [
            'status' => 'pending',
            'progress' => 0,
            'error_message' => null,
            'result_image' => null,
        ]);

        // Gửi yêu cầu đến ComfyUI
        $promptId = $this->comfyUIService->createImage($job);

        if (!$promptId) {
            throw new \Exception('Lỗi khi tạo ảnh với ComfyUI: ' . ($job->error_message ?? 'Lỗi không xác định'));
        }

        // Đưa vào queue để kiểm tra tiến trình
        dispatch(new CheckImageJobStatus($job->id, $promptId))
            ->onQueue('image-processing')
            ->delay(now()->addSeconds(15));

        return [
            'job_id' => $job->id,
            'status' => $job->status,
        ];
        }, "Retrying job ID: {$jobId} for user ID: {$user->id}");
    }

    /**
     * Xử lý upload ảnh
     */
    private function handleImageUpload(?UploadedFile $file): ?string
    {
        if (!$file) {
            return null;
        }

        return $file->store('images/inputs');
    }

    /**
     * Dọn dẹp ảnh của tiến trình
     */
    private function cleanupJobImages(ImageJob $job): void
    {
        if ($job->main_image && Storage::exists($job->main_image)) {
            Storage::delete($job->main_image);
        }

        if ($job->secondary_image && Storage::exists($job->secondary_image)) {
            Storage::delete($job->secondary_image);
        }
    }
} 