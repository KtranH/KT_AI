<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Interfaces\UserRepositoryInterface;
use App\Models\ImageJob;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CreditService extends BaseService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Trừ credits và tạo image job trong một transaction
     * Đây là cách an toàn để đảm bảo consistency
     */
    public function deductCreditsForImageJob(User $user, callable $createJobCallback): ImageJob
    {
        return $this->executeInTransactionSafely(function() use ($user, $createJobCallback) {
            // 1. Kiểm tra credits trong transaction (tránh race condition)
            $user->refresh();
            if ($user->remaining_credits < 1) {
                throw BusinessException::insufficientCredits('tạo ảnh');
            }

            // 2. Trừ credits ngay lập tức
            $user->decrement('remaining_credits');
            
            // 3. Tạo image job
            $imageJob = $createJobCallback($user);
            
            Log::info("Đã trừ 1 credit cho user {$user->id} khi tạo job {$imageJob->id}");
            
            return $imageJob;
        }, "Deducting credits for user ID: {$user->id}");
    }

    /**
     * Hoàn lại credits khi job thất bại
     */
    public function refundCreditsForFailedJob(ImageJob $job, string $reason = ''): void
    {
        if (!$job->user) {
            Log::warning("Không thể hoàn credits: Job {$job->id} không có user");
            return;
        }

        // Kiểm tra xem đã hoàn credits chưa để tránh duplicate
        if ($job->credits_refunded) {
            Log::info("Credits đã được hoàn trước đó cho job {$job->id}");
            return;
        }

        $this->executeInTransactionSafely(function() use ($job, $reason) {
            $job->user->increment('remaining_credits');
            $job->credits_refunded = true;
            $job->save();
            
            Log::info("Đã hoàn 1 credit cho user {$job->user->id} vì job {$job->id} thất bại: $reason");
        }, "Refunding credits for failed job ID: {$job->id}");
    }

    /**
     * Kiểm tra có đủ credits không
     */
    public function hasEnoughCredits(User $user, int $requiredCredits = 1): bool
    {
        return $user->remaining_credits >= $requiredCredits;
    }

    /**
     * Lấy số credits còn lại
     */
    public function getRemainingCredits(User $user): int
    {
        return $user->fresh()->remaining_credits;
    }
} 