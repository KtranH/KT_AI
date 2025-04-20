<?php

namespace App\Notifications;

use App\Models\Interaction;
use App\Models\User;
use App\Models\Image;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LikeImageNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    protected $interaction;
    protected $liker;
    protected $image;

    /**
     * Create a new notification instance.
     */
    public function __construct(Interaction $interaction, User $liker, Image $image)
    {
        $this->interaction = $interaction;
        $this->liker = $liker;
        $this->image = $image;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Có người thích ảnh của bạn')
            ->line($this->liker->name . ' đã thích ảnh ' . $this->image->title . ' của bạn.')
            ->action('Xem ảnh', url('/image/' . $this->image->id))
            ->line('Cảm ơn bạn đã sử dụng ứng dụng của chúng tôi!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'interaction_id' => $this->interaction->id,
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'liker_avatar' => $this->liker->avatar_url ?? null,
            'image_id' => $this->image->id,
            'image_title' => $this->image->title,
            'image_url' => is_string($this->image->image_url) 
                ? json_decode($this->image->image_url, true)[0] ?? null 
                : $this->image->image_url[0] ?? null,
            'type' => 'like_image',
            'message' => $this->liker->name . ' đã thích ảnh ' . $this->image->title . ' của bạn.'
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'notification_id' => $this->id,
            'interaction_id' => $this->interaction->id,
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'liker_avatar' => $this->liker->avatar_url ?? null,
            'image_id' => $this->image->id,
            'image_title' => $this->image->title,
            'image_url' => is_string($this->image->image_url) 
                ? json_decode($this->image->image_url, true)[0] ?? null 
                : $this->image->image_url[0] ?? null,
            'type' => 'like_image',
            'message' => $this->liker->name . ' đã thích ảnh ' . $this->image->title . ' của bạn.',
            'created_at' => now()->toIso8601String()
        ]);
    }

    /**
     * Get the type of the notification being broadcast.
     *
     * @return string
     */
    public function broadcastType(): string
    {
        return 'like.image';
    }
} 