<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddCommentNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;
    protected $liker;
    protected $comment;
    /**
     * Tạo mới một instance của notification
     */
    public function __construct(User $liker, Comment $comment)
    {
        //
        $this->liker = $liker;
        $this->comment = $comment;
    }

    /**
     * Lấy các kênh giao tiếp cho notification
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Lấy biểu diễn email của notification
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Có người bình luận trên hình ảnh của bạn')
            ->line($this->liker->name . ' đã bình luận trên hình ảnh "' . $this->comment->image->title . '" của bạn.')
            ->action('Xem bình luận', url('/comment/' . $this->comment->id))
            ->line('Cảm ơn bạn đã sử dụng ứng dụng của chúng tôi!');
    }

    /**
     * Lấy biểu diễn mảng của notification
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $likerAvatar = $this->takeAvatarUser($this->liker);
        return [
            //
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'liker_avatar' => $likerAvatar,
            'image_id' => $this->comment->image_id,
            'comment_id' => $this->comment->id,
            'comment_content' => $this->comment->content,
            'type' => 'add_comment',
            'message' => $this->liker->name . ' đã bình luận trên hình ảnh ' . $this->comment->image->title . ' của bạn.',
            'created_at' => now()->toIso8601String(),
            'read_at' => null,
        ];
    }
    /**
     * Lấy biểu diễn broadcastable của notification
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
            'type' => 'add_comment',
            'message' => $this->liker->name . ' đã bình luận trên hình ảnh ' . $this->comment->image->title . ' của bạn.',
            'created_at' => now()->toIso8601String(),
            'read_at' => null,
        ]);
    }
    /**
     * Lấy kiểu broadcast của notification
     */
    public function broadcastType(): string
    {
        return 'add.comment';
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
