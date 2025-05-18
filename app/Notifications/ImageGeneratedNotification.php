<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ImageJob;

class ImageGeneratedNotification extends Notification
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
            'title' => 'Ảnh đã được tạo thành công',
            'message' => 'Tiến trình tạo ảnh "' . mb_substr($this->imageJob->prompt, 0, 50) . (strlen($this->imageJob->prompt) > 50 ? '...' : '') . '" đã hoàn thành.',
            'image' => $this->imageJob->result_image_url,
            'action_text' => 'Xem ảnh',
            'action_url' => '/image-jobs',
            'created_at' => $this->imageJob->updated_at
        ];
    }
}
