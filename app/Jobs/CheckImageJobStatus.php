<?php

namespace App\Jobs;

use App\Models\ImageJob;
use App\Services\ComfyUIService;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckImageJobStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 3;
    public $maxExceptions = 3;
    public $backoff = [10, 30, 60]; // Thời gian chờ giữa các lần thử lại (seconds)
    
    protected $imageJobId;
    protected $comfyPromptId;
    protected $attempts = 0;
    protected $maxAttempts = 10; // Tối đa 10 lần kiểm tra
    
    /**
     * Tạo mới một job kiểm tra tiến trình tạo ảnh
     * @param int $imageJobId ID của job tạo ảnh
     * @param string $comfyPromptId ID của prompt tạo ảnh
     * @param int $attempts Số lần thử lại
     */
    public function __construct(int $imageJobId, string $comfyPromptId, int $attempts = 0)
    {
        $this->imageJobId = $imageJobId;
        $this->comfyPromptId = $comfyPromptId;
        $this->attempts = $attempts;
        $this->onQueue('image-processing');
    }

    /**
     * Thực thi job
     * @param ComfyUIService $comfyuiService Dịch vụ ComfyUI
     * @return void
     */
    public function handle(ComfyUIService $comfyuiService): void
    {
        // Gọi Interface UserRepository
        $userRepository = app(UserRepositoryInterface::class);
        
        // Lấy thông tin job từ database
        $imageJob = ImageJob::find($this->imageJobId);
        
        // Kiểm tra nếu không tìm thấy job hoặc job đã hoàn thành/thất bại
        if (!$imageJob || in_array($imageJob->status, ['completed', 'failed'])) {
            Log::info("Bỏ qua kiểm tra tiến trình {$this->imageJobId} vì đã xử lý hoặc không tồn tại");
            return;
        }
        
        $attemptNumber = $this->attempts + 1;
        Log::info("Kiểm tra tiến trình {$imageJob->id} với comfy_prompt_id: {$this->comfyPromptId} (lần thử {$attemptNumber})");
        
        try {
            // Kiểm tra trạng thái từ ComfyUI
            $historyData = $comfyuiService->checkJobStatus($this->comfyPromptId);
            
            // Kiểm tra xem có lỗi trong history data không.
            if (isset($historyData[$this->comfyPromptId]['status']['status_str']) && 
                $historyData[$this->comfyPromptId]['status']['status_str'] === 'error') {
                $errorMessage = $historyData[$this->comfyPromptId]['status']['error'] ?? 'Lỗi không xác định';
                
                $imageJob->status = 'failed';
                $imageJob->error_message = 'Lỗi từ ComfyUI: ' . $errorMessage;
                $imageJob->save();
                
                // Gửi thông báo lỗi cho người dùng
                $user = $imageJob->user;
                if ($user && $imageJob instanceof ImageJob) {
                    $user->notify(new \App\Notifications\ImageGenerationFailedNotification($imageJob));
                    Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
                }
                
                // Tăng số lượng credits khi người dùng tạo ảnh thất bại
                $userRepository->increaseCredits($user->id);          

                Log::error("Tiến trình {$imageJob->id} thất bại với lỗi: " . $errorMessage);
                return;
            }
            
            // Kiểm tra trạng thái success/completed trong history
            $isCompleted = false;
            
            // Kiểm tra các trường hợp job hoàn thành
            if (!isset($historyData['error'])) {
                // Kiểm tra status_str
                if (isset($historyData[$this->comfyPromptId]['status']['status_str']) && 
                    $historyData[$this->comfyPromptId]['status']['status_str'] === 'success') {
                    $isCompleted = true;
                    Log::info("Tiến trình {$imageJob->id} đã hoàn thành (status=success)");
                }
                // Kiểm tra completed flag
                elseif (isset($historyData[$this->comfyPromptId]['status']['completed']) && 
                        $historyData[$this->comfyPromptId]['status']['completed'] === true) {
                    $isCompleted = true;
                    Log::info("Tiến trình {$imageJob->id} đã hoàn thành (completed=true)");
                }
                // Kiểm tra outputs có chứa images không
                elseif (isset($historyData[$this->comfyPromptId]['outputs'])) {
                    foreach ($historyData[$this->comfyPromptId]['outputs'] as $nodeOutput) {
                        if (isset($nodeOutput['images']) && !empty($nodeOutput['images'])) {
                            // Ưu tiên kiểm tra ảnh có type=output
                            $hasOutputImage = false;
                            foreach ($nodeOutput['images'] as $image) {
                                if (isset($image['type']) && $image['type'] === 'output') {
                                    $hasOutputImage = true;
                                    break;
                                }
                            }
                            
                            if ($hasOutputImage) {
                                $isCompleted = true;
                                Log::info("Tiến trình {$imageJob->id} đã hoàn thành (có images type=output trong outputs)");
                                break;
                            } else if (!isset($nodeOutput['images'][0]['type'])) {
                                $isCompleted = true;
                                Log::info("Tiến trình {$imageJob->id} đã hoàn thành (có images không có type trong outputs)");
                                break;
                            }
                            // Không đánh dấu hoàn thành nếu chỉ tìm thấy ảnh có type khác (ví dụ: temp)
                        }
                    }
                }
                // Kiểm tra hasImagesInHistoryData
                elseif (method_exists($comfyuiService, 'hasImagesInHistoryData') && 
                        $comfyuiService->hasImagesInHistoryData($historyData)) {
                    $isCompleted = true;
                    Log::info("Tiến trình {$imageJob->id} đã hoàn thành (hasImagesInHistoryData)");
                }
            }
            // Kiểm tra nếu có lỗi trong history
            else if (isset($historyData['error'])) {
                $imageJob->status = 'failed';
                $imageJob->error_message = 'Lỗi từ ComfyUI: ' . $historyData['error'];
                $imageJob->save();
                
                // Gửi thông báo lỗi cho người dùng
                $user = $imageJob->user;
                if ($user && $imageJob instanceof ImageJob) {
                    $user->notify(new \App\Notifications\ImageGenerationFailedNotification($imageJob));
                    Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
                }
                
                // Tăng số lượng credits khi người dùng tạo ảnh thất bại
                $userRepository->increaseCredits($user->id);          

                Log::error("Tiến trình {$imageJob->id} thất bại với lỗi: " . $historyData['error']);
                return;
            }
            
            // Nếu job đã hoàn thành, cập nhật thông tin
            if ($isCompleted) {
                Log::info("Cập nhật tiến trình {$imageJob->id} sang trạng thái hoàn thành");
                if ($imageJob instanceof ImageJob) {
                    $comfyuiService->updateCompletedJob($imageJob, $historyData);
                    
                    // Giảm số lượng credits khi người dùng tạo ảnh thành công
                    $user = $imageJob->user;
                    if ($user) {
                        $userRepository->decreaseCredits($user->id);
                        Log::info("Đã giảm credits cho người dùng {$user->id} sau khi tạo ảnh thành công");
                    }
                }
                return;
            }
            
            // Kiểm tra tiến độ job
            $progressData = $comfyuiService->checkJobProgress($this->comfyPromptId);
            
            // Kiểm tra nếu có lỗi từ progress
            if ($progressData['status'] === 'failed' || $progressData['status'] === 'error') {
                $imageJob->status = 'failed';
                $imageJob->error_message = $progressData['error'] ?? 'Lỗi không xác định từ ComfyUI';
                $imageJob->save();
                
                // Gửi thông báo lỗi cho người dùng
                $user = $imageJob->user;
                if ($user && $imageJob instanceof ImageJob) {
                    $user->notify(new \App\Notifications\ImageGenerationFailedNotification($imageJob));
                    Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
                }
                
                // Tăng số lượng credits khi người dùng tạo ảnh thất bại
                $userRepository->increaseCredits($user->id);          

                Log::error("Tiến trình {$imageJob->id} thất bại với lỗi: " . $imageJob->error_message);
                return;
            }
            
            // Cập nhật tiến độ nếu có thay đổi
            if (isset($progressData['progress']) && $progressData['progress'] != $imageJob->progress) {
                $imageJob->progress = $progressData['progress'];
                $imageJob->save();
                Log::info("Đã cập nhật tiến độ tiến trình {$imageJob->id}: {$imageJob->progress}%");
            }
            
            // Cập nhật trạng thái job
            if ($progressData['status'] === 'completed') {
                Log::info("Tiến trình {$imageJob->id} đã hoàn thành theo progress API");
                if ($imageJob instanceof ImageJob) {
                    $comfyuiService->updateCompletedJob($imageJob, $historyData);
                    
                    // Giảm số lượng credits khi người dùng tạo ảnh thành công
                    $user = $imageJob->user;
                    if ($user) {
                        $userRepository->decreaseCredits($user->id);
                        Log::info("Đã giảm credits cho người dùng {$user->id} sau khi tạo ảnh thành công");
                    }
                }
            } 
            else if ($progressData['status'] === 'processing' && $imageJob->status === 'pending') {
                $imageJob->status = 'processing';
                $imageJob->save();
                Log::info("Đã cập nhật tiến trình {$imageJob->id} từ pending sang processing");
            }
            
            // Nếu chưa hoàn thành và chưa vượt quá số lần thử, đưa lại vào queue
            if (!in_array($imageJob->status, ['completed', 'failed']) && $this->attempts < $this->maxAttempts) {
                // Tăng dần thời gian delay giữa các lần kiểm tra
                $delay = min(30 + ($this->attempts * 10), 120); // Tăng từ 30s đến tối đa 120s
                
                $nextAttempt = $this->attempts + 1;
                // Dispatch lại job với attempts tăng thêm 1
                self::dispatch($this->imageJobId, $this->comfyPromptId, $nextAttempt)
                    ->delay(now()->addSeconds($delay))
                    ->onQueue('image-processing');
                
                Log::info("Đã đưa lại job kiểm tra tiến trình {$imageJob->id} vào queue với delay {$delay}s (lần thử tiếp theo: " . ($nextAttempt + 1) . ")");
            } 
            else if ($this->attempts >= $this->maxAttempts && !in_array($imageJob->status, ['completed', 'failed'])) {
                // Nếu vượt quá số lần thử, đánh dấu job thất bại
                $imageJob->status = 'failed';
                $imageJob->error_message = 'Quá thời gian xử lý, tiến trình đã bị hủy';
                $imageJob->save();
                
                // Gửi thông báo lỗi cho người dùng
                $user = $imageJob->user;
                if ($user && $imageJob instanceof ImageJob) {
                    $user->notify(new \App\Notifications\ImageGenerationFailedNotification($imageJob));
                    Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
                }
                
                // Tăng số lượng credits khi người dùng tạo ảnh thất bại
                $userRepository->increaseCredits($user->id);          

                Log::warning("Tiến trình {$imageJob->id} đã bị hủy do quá thời gian xử lý (sau {$this->attempts} lần thử)");
            }
            
        } catch (\Exception $e) {
            Log::error("Lỗi khi kiểm tra tiến trình {$imageJob->id}: " . $e->getMessage());
            
            // Nếu lỗi và vượt quá số lần thử, đánh dấu job thất bại
            if ($this->attempts >= $this->maxAttempts - 1) {
                $imageJob->status = 'failed';
                $imageJob->error_message = 'Lỗi khi kiểm tra tiến trình: ' . $e->getMessage();
                $imageJob->save();
                
                // Gửi thông báo lỗi cho người dùng
                $user = $imageJob->user;
                if ($user && $imageJob instanceof ImageJob) {
                    $user->notify(new \App\Notifications\ImageGenerationFailedNotification($imageJob));
                    Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
                }

                // Tăng số lượng credits khi người dùng tạo ảnh thất bại
                $userRepository->increaseCredits($user->id);          
                
                Log::warning("Tiến trình {$imageJob->id} đã bị đánh dấu thất bại do lỗi sau {$this->attempts} lần thử");
            } else {
                // Nếu còn cơ hội thử lại, release job
                $this->release(30);
            }
        }
    }
}
