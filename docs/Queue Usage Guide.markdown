# Hướng Dẫn Sử Dụng Queue Trong Dự Án KT_AI

## Giới Thiệu

Queue (Hàng đợi) là một thành phần quan trọng trong Laravel, giúp xử lý các tác vụ nặng, tốn thời gian theo cách không đồng bộ. Thay vì người dùng phải đợi các tác vụ hoàn thành, hệ thống sẽ đưa chúng vào hàng đợi và xử lý sau, giúp tăng trải nghiệm người dùng và hiệu suất ứng dụng.

Trong dự án KT_AI, queue được sử dụng chủ yếu để xử lý các tiến trình sinh ảnh bằng AI thông qua ComfyUI, vốn là một tác vụ tốn thời gian. Tài liệu này sẽ giải thích chi tiết về cách queue được triển khai và sử dụng trong dự án.

## Cấu Hình Queue

### Loại Queue

Dự án KT_AI sử dụng **database queue driver**, lưu trữ các job trong cơ sở dữ liệu. Cấu hình này được định nghĩa trong file `config/queue.php`:

```php
'default' => env('QUEUE_CONNECTION', 'database'),

'connections' => [
    // ...
    'database' => [
        'driver' => 'database',
        'connection' => env('DB_QUEUE_CONNECTION'),
        'table' => env('DB_QUEUE_TABLE', 'jobs'),
        'queue' => env('DB_QUEUE', 'default'),
        'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
        'after_commit' => false,
    ],
    // ...
],
```

### Cấu Trúc Bảng Queue

Dự án sử dụng bảng `jobs` để lưu trữ các job đang chờ xử lý, được tạo thông qua migration:

```php
Schema::create('jobs', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('queue')->index();
    $table->longText('payload');
    $table->unsignedTinyInteger('attempts');
    $table->unsignedInteger('reserved_at')->nullable();
    $table->unsignedInteger('available_at');
    $table->unsignedInteger('created_at');
});
```

### Các Queue Được Sử Dụng

Dự án sử dụng 3 queue chính:
1. `default` - Queue mặc định cho các tác vụ chung
2. `image-processing` - Queue ưu tiên cao dành cho xử lý ảnh
3. `image-processing-low` - Queue ưu tiên thấp cho các kiểm tra trạng thái

## Tạo Và Định Nghĩa Job

### Cấu Trúc Của Một Job

Jobs trong Laravel đều triển khai interface `ShouldQueue` và sử dụng các traits như `Dispatchable`, `InteractsWithQueue`, `Queueable`, và `SerializesModels`. Ví dụ về job `CheckImageJobStatus`:

```php
class CheckImageJobStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // Thời gian tối đa cho phép job chạy (giây)
    public $tries = 3; // Số lần thử lại nếu job thất bại
    public $maxExceptions = 3; // Số lần ngoại lệ tối đa trước khi đánh dấu job thất bại
    public $backoff = [10, 30, 60]; // Thời gian chờ giữa các lần thử lại (giây)
    
    protected $imageJobId;
    protected $comfyPromptId;
    protected $attempts = 0;
    protected $maxAttempts = 10; // Tối đa 10 lần kiểm tra
    
    public function __construct(int $imageJobId, string $comfyPromptId, int $attempts = 0)
    {
        $this->imageJobId = $imageJobId;
        $this->comfyPromptId = $comfyPromptId;
        $this->attempts = $attempts;
        $this->onQueue('image-processing'); // Chỉ định queue cho job
    }

    public function handle(ComfyUIService $comfyuiService): void
    {
        // Logic xử lý job
    }
}
```

### Đặc Điểm Quan Trọng Của Job

1. **Timeout**: Thời gian tối đa để job hoàn thành trước khi bị hủy
2. **Tries**: Số lần Laravel queue worker sẽ thử lại job nếu nó thất bại
3. **BackOff**: Thời gian chờ giữa các lần thử lại
4. **maxExceptions**: Số lần ngoại lệ tối đa trước khi đánh dấu job thất bại hoàn toàn
5. **Queue**: Tên queue mà job sẽ được gửi đến

## Dispatch Job Vào Queue

### Cách Thêm Job Vào Queue

Có hai cách chính để thêm job vào queue:

#### 1. Sử Dụng Phương Thức dispatch() Của Job:

```php
CheckImageJobStatus::dispatch($imageJob->id, $promptId)
    ->onQueue('image-processing')
    ->delay(now()->addSeconds(15));
```

#### 2. Sử Dụng Hàm dispatch() Toàn Cục:

```php
dispatch(new CheckImageJobStatus($job->id, $job->comfy_prompt_id))
    ->onQueue('image-processing-low')
    ->delay(now()->addSeconds(5));
```

### Các Tùy Chọn Khi Dispatch Job

1. **onQueue()**: Chỉ định queue cụ thể
2. **delay()**: Trì hoãn xử lý job một khoảng thời gian
3. **afterCommit()**: Chỉ xử lý job sau khi transaction cơ sở dữ liệu được commit
4. **afterResponse()**: Chỉ xử lý job sau khi response đã được gửi đến người dùng

## Xử Lý Logic Trong Job

Trong dự án KT_AI, job `CheckImageJobStatus` được sử dụng để kiểm tra tiến trình sinh ảnh:

1. **Lấy dữ liệu tiến trình**:
   ```php
   $imageJob = ImageJob::find($this->imageJobId);
   ```

2. **Kiểm tra trạng thái**:
   ```php
   $historyData = $comfyuiService->checkJobStatus($this->comfyPromptId);
   $progressData = $comfyuiService->checkJobProgress($this->comfyPromptId);
   ```

3. **Cập nhật dữ liệu**:
   ```php
   $imageJob->progress = $progressData['progress'];
   $imageJob->save();
   ```

4. **Xử lý khi hoàn thành**:
   ```php
   if ($isCompleted) {
       $comfyuiService->updateCompletedJob($imageJob, $historyData);
   }
   ```

5. **Tự động lên lịch lại**:
   ```php
   if (!in_array($imageJob->status, ['completed', 'failed']) && $this->attempts < $this->maxAttempts) {
       $delay = min(30 + ($this->attempts * 10), 120);
       self::dispatch($this->imageJobId, $this->comfyPromptId, $nextAttempt)
           ->delay(now()->addSeconds($delay))
           ->onQueue('image-processing');
   }
   ```

## Chạy Queue Worker

### Lệnh Mặc Định Của Laravel

```bash
php artisan queue:work --queue=default,image-processing,image-processing-low
```

### Command Tùy Chỉnh Trong Dự Án

Dự án KT_AI có một command tùy chỉnh `StartQueueWorker` để quản lý queue worker:

```bash
php artisan queue:start-worker --conn=database --queue=default,image-processing,image-processing-low --sleep=3 --tries=3 --timeout=60 --daemon
```

Trong đó:
- **conn**: Kết nối queue (database, redis, ...)
- **queue**: Danh sách các queue cần xử lý (phân tách bằng dấu phẩy, theo thứ tự ưu tiên)
- **sleep**: Thời gian nghỉ (giây) khi không có job
- **tries**: Số lần thử lại các job thất bại
- **timeout**: Thời gian tối đa (giây) cho phép mỗi job chạy
- **daemon**: Chạy như một tiến trình nền

### Khởi Động Queue Worker Như Một Tiến Trình Nền

```bash
php artisan queue:start-worker --daemon
```

### Giám Sát Queue Worker

Các queue worker nên được giám sát bằng supervisor hoặc công cụ tương tự để đảm bảo chúng hoạt động liên tục. Nếu queue worker bị crash, supervisor sẽ tự động khởi động lại.

## Sử Dụng Queue Trong Dự Án KT_AI

### Luồng Xử Lý Của Hệ Thống

1. Người dùng gửi yêu cầu tạo ảnh
2. Hệ thống tạo một bản ghi `ImageJob` với trạng thái `pending`
3. Gửi yêu cầu đến ComfyUI và nhận về một prompt ID
4. Thêm job `CheckImageJobStatus` vào queue để kiểm tra trạng thái
5. Queue worker xử lý job, kiểm tra tiến trình với ComfyUI
6. Nếu tiến trình chưa hoàn thành, job tự lên lịch lại với một độ trễ
7. Nếu tiến trình hoàn thành, cập nhật bản ghi `ImageJob` và lưu kết quả
8. Người dùng có thể kiểm tra trạng thái và kết quả tiến trình

### Ví Dụ Về Sử Dụng Queue

Từ controller `ImageJobController`:

```php
// Tạo yêu cầu sinh ảnh
$promptId = $this->comfyuiService->createImage($job);

// Đưa vào queue để kiểm tra tiến trình
dispatch(new CheckImageJobStatus($job->id, $promptId))
    ->onQueue('image-processing')
    ->delay(now()->addSeconds(15));
```

## Xử Lý Lỗi Và Thất Bại

### Job Thất Bại

Nếu một job gặp ngoại lệ không xử lý được, Laravel sẽ:
1. Thử lại job theo số lần được chỉ định trong `$tries`
2. Nếu vẫn thất bại, job sẽ được chuyển vào bảng `failed_jobs`

### Xử Lý Job Thất Bại

```php
if ($this->attempts >= $this->maxAttempts) {
    $imageJob->status = 'failed';
    $imageJob->error_message = 'Quá thời gian xử lý, tiến trình đã bị hủy';
    $imageJob->save();
    
    // Gửi thông báo lỗi cho người dùng
    $user = $imageJob->user;
    if ($user) {
        $user->notify(new \App\Notifications\ImageGenerationFailedNotification($imageJob));
    }
}
```

### Thử Lại Job Thất Bại

```bash
php artisan queue:retry all  # Thử lại tất cả job thất bại
php artisan queue:retry 5    # Thử lại job thất bại với ID là 5
```

## Thực Hành Tốt Nhất

1. **Job nên ngắn gọn**: Mỗi job nên thực hiện một nhiệm vụ cụ thể
2. **Xử lý ngoại lệ**: Luôn xử lý các ngoại lệ có thể xảy ra
3. **Giới hạn thời gian**: Đặt timeout hợp lý cho các job
4. **Luôn giám sát queue**: Sử dụng các công cụ như supervisor
5. **Dùng transaction**: Khi job thao tác nhiều bảng trong cơ sở dữ liệu
6. **Không dựa vào Request/Session**: Job có thể chạy trong một tiến trình hoặc máy chủ khác

## Kết Luận

Queue là một công cụ mạnh mẽ trong Laravel để xử lý các tác vụ nặng theo cách không đồng bộ. Trong dự án KT_AI, queue đóng vai trò quan trọng trong việc xử lý các tiến trình sinh ảnh AI, giúp tăng trải nghiệm người dùng và hiệu suất hệ thống.

Để mở rộng và tối ưu hóa hệ thống queue, các bước tiếp theo có thể bao gồm:
1. Sử dụng Redis queue để có hiệu suất cao hơn
2. Triển khai Horizon để quản lý và giám sát queue
3. Tách các job thành nhiều queue nhỏ hơn với độ ưu tiên khác nhau
4. Cân nhắc các chiến lược retry và back-off phức tạp hơn