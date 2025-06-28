<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class StartQueueWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:start-worker
                            {--conn=database : Kết nối queue sử dụng}
                            {--queue=default,image-processing,image-processing-low : Tên queue cần xử lý}
                            {--sleep=3 : Số giây nghỉ khi không có job}
                            {--tries=3 : Số lần thử lại khi job lỗi}
                            {--timeout=60 : Thời gian tối đa cho mỗi job}
                            {--daemon : Chạy dưới dạng daemon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Khởi động queue worker để xử lý các tiến trình bất đồng bộ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $command = 'php artisan queue:work';
        
        // Thêm các tùy chọn
        $command .= ' --conn=' . $this->option('conn');
        $command .= ' --queue=' . $this->option('queue');
        $command .= ' --sleep=' . $this->option('sleep');
        $command .= ' --tries=' . $this->option('tries');
        $command .= ' --timeout=' . $this->option('timeout');
        
        if ($this->option('daemon')) {
            $command .= ' --daemon';
        }
        
        $this->info('Khởi động queue worker với lệnh: ' . $command);
        
        if ($this->option('daemon')) {
            // Chạy dưới dạng process riêng biệt
            $process = new Process(explode(' ', $command), base_path());
            $process->disableOutput();
            $process->start();
            
            if ($process->isRunning()) {
                $this->info('Đã khởi động queue worker dưới dạng daemon. PID: ' . $process->getPid());
            } else {
                $this->error('Không thể khởi động queue worker: ' . $process->getErrorOutput());
            }
        } else {
            // Chạy trực tiếp
            $this->info('Bắt đầu xử lý queue. Nhấn Ctrl+C để dừng.');
            $this->call('queue:work', [
                '--conn' => $this->option('conn'),
                '--queue' => $this->option('queue'),
                '--sleep' => $this->option('sleep'),
                '--tries' => $this->option('tries'),
                '--timeout' => $this->option('timeout'),
            ]);
        }
        
        return Command::SUCCESS;
    }
}
