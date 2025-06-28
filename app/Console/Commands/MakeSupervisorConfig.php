<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeSupervisorConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:make-supervisor-config 
                            {--file=laravel-worker : Tên file cấu hình}
                            {--conn=database : Kết nối queue}
                            {--queue=default,image-processing,image-processing-low : Tên queue cần xử lý}
                            {--processes=2 : Số lượng process}
                            {--memory=128 : Giới hạn bộ nhớ (MB)}
                            {--output=/tmp/worker.log : Đường dẫn file log}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo file cấu hình Supervisor cho queue worker';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->option('file');
        $connection = $this->option('conn');
        $queue = $this->option('queue');
        $processes = $this->option('processes');
        $memory = $this->option('memory');
        $output = $this->option('output');
        
        $configContent = <<<EOT
[program:{$filename}]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work {$connection} --queue={$queue} --sleep=3 --tries=3 --memory={$memory} --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs={$processes}
redirect_stderr=true
stdout_logfile={$output}
stopwaitsecs=3600

[supervisord]
logfile=/tmp/supervisord.log
logfile_maxbytes=50MB
logfile_backups=10
loglevel=info
EOT;

        // Lưu file cấu hình
        $configPath = storage_path("supervisor/{$filename}.conf");
        $dir = dirname($configPath);
        
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        
        File::put($configPath, $configContent);
        
        $this->info("Đã tạo file cấu hình Supervisor tại: {$configPath}");
        $this->info("Để sử dụng cấu hình này trên máy chủ production, hãy:");
        $this->info("1. Sao chép file này vào /etc/supervisor/conf.d/{$filename}.conf");
        $this->info("2. Chạy lệnh: sudo supervisorctl reread");
        $this->info("3. Chạy lệnh: sudo supervisorctl update");
        $this->info("4. Kiểm tra trạng thái: sudo supervisorctl status");
        
        return Command::SUCCESS;
    }
} 