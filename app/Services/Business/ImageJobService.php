<?php

declare(strict_types=1);

namespace App\Services\Business;

use App\Interfaces\ImageJobRepositoryInterface;
use App\Jobs\CheckImageJobStatus;
use App\Models\ImageJob;
use App\Models\User;
use App\Services\External\ComfyUI\ComfyuiService;
use App\Services\Business\CreditService;
use App\Services\BaseService;
use App\Http\Resources\V1\Content\ImageJobCollection;
use App\Http\Resources\V1\Content\ImageJobResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ImageJobService extends BaseService
{
    protected ImageJobRepositoryInterface $imageJobRepository;
    protected ComfyuiService $comfyUIService;
    protected CreditService $creditService;

    public function __construct(
        ImageJobRepositoryInterface $imageJobRepository,
        ComfyuiService $comfyUIService,
        CreditService $creditService,
    ) {
        $this->imageJobRepository = $imageJobRepository;
        $this->comfyUIService = $comfyUIService;
        $this->creditService = $creditService;
    }

    /**
     * Tạo tiến trình tạo ảnh mới
     */
    public function createImageJob(array $data, User $user): array
    {
        return $this->executeWithExceptionHandling(function() use ($data, $user) {
            // Kiểm tra giới hạn tiến trình
            $activeJobsCount = $this->imageJobRepository->countActiveJobsByUser($user->id);
            if ($activeJobsCount >= 5) {
                throw ValidationException::withMessages([
                    'limit' => ['Bạn đã đạt đến giới hạn 5 tiến trình đang hoạt động. Vui lòng đợi cho đến khi một số tiến trình hoàn thành.']
                ]);
            }

            // Sử dụng CreditService để trừ credits và tạo job atomically
            $imageJob = $this->creditService->deductCreditsForImageJob($user, function($user) use ($data) {
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
                    'progress' => 0, // Xem tiến trình tạo ảnh
                    'credits_refunded' => false,
                ];

                return $this->imageJobRepository->create($jobData);
            });

            // Gửi yêu cầu đến ComfyUI
            $promptId = $this->comfyUIService->createImage($imageJob);

            if (!$promptId) {
                // Nếu tạo ảnh thất bại, hoàn lại credits
                $this->creditService->refundCreditsForFailedJob($imageJob, 'Lỗi khi gửi yêu cầu tới ComfyUI');
                throw new \Exception('Lỗi khi tạo ảnh với ComfyUI: ' . ($imageJob->error_message ?? 'Lỗi không xác định'));
            }

            // Đưa vào queue để kiểm tra tiến trình
            dispatch(new CheckImageJobStatus($imageJob->id, $promptId))
                ->onQueue('image-processing')
                ->delay(now()->addSeconds(15));
                
            return [
                'job' => new ImageJobResource($imageJob),
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
            'active_jobs' => ImageJobResource::collection($activeJobs),
            'count' => $activeJobs->count(),
        ];
        }, "Getting active jobs for user ID: {$user->id}");
    }

    /**
     * Lấy danh sách tiến trình đã hoàn thành
     */
    public function getCompletedJobs(Request $request): ImageJobCollection
    {
        return $this->executeWithExceptionHandling(function() use ($request) {
            $user = Auth::user();
            $perPage = (int)$request->input('per_page', 10);
            $page = (int)$request->input('page', 1);
            
            // Lấy Query Builder từ model và phân trang
            $query = $this->imageJobRepository->getCompletedJobsByUser($user->id);
            $jobs = $query->paginate($perPage, ['*'], 'page', $page);

            return new ImageJobCollection($jobs);
        }, "Getting completed jobs for user ID: " . Auth::id());
    }

    /**
     * Lấy danh sách tiến trình thất bại
     */
    public function getFailedJobs(Request $request): ImageJobCollection
    {
        return $this->executeWithExceptionHandling(function() use ($request) {
            $user = Auth::user();
            $perPage = (int)$request->input('per_page', 10);
            $page = (int)$request->input('page', 1);
            
            $query = $this->imageJobRepository->getFailedJobsByUser($user->id);
            $jobs = $query->paginate($perPage, ['*'], 'page', $page);

            return new ImageJobCollection($jobs);
        }, "Getting failed jobs for user ID: " . Auth::id());
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
            'job' => new ImageJobResource($job)
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

        // Hoàn lại credits khi user hủy job
        $this->creditService->refundCreditsForFailedJob($job, 'Tiến trình đã bị hủy bởi người dùng');

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