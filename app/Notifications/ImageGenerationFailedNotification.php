<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ImageJob;

class ImageGenerationFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $imageJob;

    /**
     * Tạo mới một thông báo tạo ảnh thất bại
     * @param ImageJob $imageJob Công việc tạo ảnh
     */
    public function __construct(ImageJob $imageJob)
    {
        $this->imageJob = $imageJob;
    }

    /**
     * Lấy các kênh gửi thông báo
     * @param object $notifiable Người nhận thông báo
     * @return array<int, string> Danh sách kênh gửi thông báo
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Lấy biểu diễn mảng của thông báo
     * @param object $notifiable Người nhận thông báo
     * @return array<string, mixed> Biểu diễn mảng của thông báo
     */
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->imageJob->id,
            'title' => 'Tạo ảnh thất bại',
            'message' => 'Tiến trình tạo ảnh "' . mb_substr($this->imageJob->prompt, 0, 50) . (strlen($this->imageJob->prompt) > 50 ? '...' : '') . '" đã thất bại. Lỗi: ' . mb_substr($this->imageJob->error_message, 0, 100) . (strlen($this->imageJob->error_message) > 100 ? '...' : ''),
            'error' => $this->imageJob->error_message,
            'action_text' => 'Xem chi tiết',
            'action_url' => '/image-jobs',
            'created_at' => $this->imageJob->updated_at
        ];
    }
} 