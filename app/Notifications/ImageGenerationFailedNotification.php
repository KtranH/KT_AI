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
     * Create a new notification instance.
     */
    public function __construct(ImageJob $imageJob)
    {
        $this->imageJob = $imageJob;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
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