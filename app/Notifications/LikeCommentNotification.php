<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;

class LikeCommentNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;
    protected $liker;
    protected $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $liker, Comment $comment)
    {
        //
        $this->liker = $liker;
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
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
            ->subject('Có người thích bình luận của bạn')
            ->line($this->liker->name . ' đã thích bình luận ' . $this->comment->content . ' của bạn.')
            ->action('Xem bình luận', url('/comment/' . $this->comment->id))
            ->line('Cảm ơn bạn đã sử dụng ứng dụng của chúng tôi!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Lấy avatar URL từ đối tượng liker đã được truyền vào
        // và gán giá trị mặc định nếu nó null hoặc rỗng.
        $likerAvatar = $this->takeAvatarUser($this->liker);
        return [
            //
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'liker_avatar' => $likerAvatar,
            'image_id' => $this->comment->image_id,
            'comment_id' => $this->comment->id,
            'comment_content' => $this->comment->content,
            'type' => 'like_comment',
            'message' => $this->liker->name . ' đã thích bình luận ' . $this->comment->content . ' của bạn.',
            'created_at' => now()->toIso8601String(),
            'read_at' => null,
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
        $likerAvatar = $this->takeAvatarUser($this->liker);
        return new BroadcastMessage([
            'notification_id' => $this->id,
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'liker_avatar' => $likerAvatar,
            'image_id' => $this->comment->image_id,
            'comment_id' => $this->comment->id,
            'comment_content' => $this->comment->content,
            'type' => 'like_comment',
            'message' => $this->liker->name . ' đã thích bình luận "' . $this->comment->content . '" của bạn.',
            'created_at' => now()->toIso8601String(),
            'read_at' => null,
        ]);
    }
    public function broadcastType(): string
    {
        return 'like.comment';
    }
    public function takeAvatarUser($liker)
    {
        $likerAvatar = $liker->avatar_url ?? "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png";
        if (empty($likerAvatar)) { // Kiểm tra thêm trường hợp rỗng sau khi dùng null coalescing
            $likerAvatar = "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png";
        }
        return $likerAvatar;
    }
}
