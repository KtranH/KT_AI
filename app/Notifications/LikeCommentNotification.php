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
     * Tạo mới một thông báo thích bình luận
     * @param User $liker Người thích bình luận
     * @param Comment $comment Bình luận được thích
     */
    public function __construct(User $liker, Comment $comment)
    {
        //
        $this->liker = $liker;
        $this->comment = $comment;
    }

    /**
     * Lấy các kênh gửi thông báo
     * @param object $notifiable Người nhận thông báo
     * @return array<int, string> Danh sách kênh gửi thông báo
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Lấy biểu diễn email của thông báo
     * @param object $notifiable Người nhận thông báo
     * @return MailMessage Biểu diễn email của thông báo
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
     * Lấy biểu diễn mảng của thông báo
     * @param object $notifiable Người nhận thông báo
     * @return array<string, mixed> Biểu diễn mảng của thông báo
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
     * Lấy biểu diễn broadcast của thông báo
     * @param object $notifiable Người nhận thông báo
     * @return BroadcastMessage Biểu diễn broadcast của thông báo
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

    /**
     * Lấy kiểu broadcast của thông báo
     * @return string Kiểu broadcast của thông báo
     */
    public function broadcastType(): string
    {
        return 'like.comment';
    }

    /**
     * Lấy avatar của người thích bình luận
     * @param User $liker Người thích bình luận
     * @return string Avatar của người thích bình luận
     */
    public function takeAvatarUser($liker)
    {
        $likerAvatar = $liker->avatar_url ?? "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png";
        if (empty($likerAvatar)) { // Kiểm tra thêm trường hợp rỗng sau khi dùng null coalescing
            $likerAvatar = "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png";
        }
        return $likerAvatar;
    }
}
