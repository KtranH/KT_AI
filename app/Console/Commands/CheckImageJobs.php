<?php

namespace App\Console\Commands;

use App\Models\ImageJob;
use App\Services\ComfyUIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckImageJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image-jobs:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra và cập nhật trạng thái của các tiến trình tạo ảnh đang xử lý';

    /**
     * Execute the console command.
     */
    public function handle(ComfyUIService $comfyuiService)
    {
        $this->info('Bắt đầu kiểm tra tiến trình tạo ảnh...');
        
        try {
            // Lấy các tiến trình đang xử lý
            $processingJobs = ImageJob::where('status', 'processing')
                ->whereNotNull('comfy_prompt_id')
                ->get();
            
            $this->info("Tìm thấy {$processingJobs->count()} tiến trình đang xử lý");
            
            $updated = 0;
            $failed = 0;
            
            foreach ($processingJobs as $job) {
                try {
                    // Kiểm tra trạng thái từ ComfyUI
                    $historyData = $comfyuiService->checkJobStatus($job->comfy_prompt_id);
                    
                    // Nếu không có lỗi và có dữ liệu outputs
                    if (!isset($historyData['error']) && isset($historyData['outputs'])) {
                        if ($comfyuiService->updateCompletedJob($job, $historyData)) {
                            $updated++;
                            $this->info("Tiến trình ID: {$job->id} đã hoàn thành");
                        }
                    }
                } catch (\Exception $e) {
                    $failed++;
                    Log::error("Lỗi khi cập nhật tiến trình {$job->id}: " . $e->getMessage());
                    $this->error("Lỗi khi cập nhật tiến trình {$job->id}: " . $e->getMessage());
                }
            }
            
            $this->info("Đã cập nhật thành công {$updated} tiến trình");
            
            if ($failed > 0) {
                $this->warn("Có {$failed} tiến trình lỗi khi cập nhật");
            }
            
            // Kiểm tra các tiến trình đã quá hạn
            $pendingJobs = ImageJob::where('status', 'pending')
                ->where('created_at', '<', now()->subHours(1))
                ->get();
            
            if ($pendingJobs->count() > 0) {
                $this->warn("Tìm thấy {$pendingJobs->count()} tiến trình đang chờ quá 1 giờ");
                
                foreach ($pendingJobs as $job) {
                    $job->status = 'failed';
                    $job->error_message = 'Tiến trình đã quá thời gian chờ (1 giờ)';
                    $job->save();
                    
                    $this->info("Đã đánh dấu tiến trình {$job->id} là thất bại do quá thời gian chờ");
                }
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error('Lỗi khi kiểm tra tiến trình tạo ảnh: ' . $e->getMessage());
            $this->error('Lỗi khi kiểm tra tiến trình tạo ảnh: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
} 