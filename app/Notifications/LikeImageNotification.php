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

class LikeImageNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    protected $interaction;
    protected $liker;
    protected $image;

    /**
     * Tạo mới một thông báo thích ảnh
     * @param Interaction $interaction Tương tác giữa người dùng và ảnh
     * @param User $liker Người thích ảnh
     * @param Image $image Ảnh được thích
     */
    public function __construct(Interaction $interaction, User $liker, Image $image)
    {
        $this->interaction = $interaction;
        $this->liker = $liker;
        $this->image = $image;
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
            ->subject('Có người thích ảnh của bạn')
            ->line($this->liker->name . ' đã thích ảnh ' . $this->image->title . ' của bạn.')
            ->action('Xem ảnh', url('/image/' . $this->image->id))
            ->line('Cảm ơn bạn đã sử dụng ứng dụng của chúng tôi!');
    }

    /**
     * Lấy biểu diễn mảng của thông báo
     * @param object $notifiable Người nhận thông báo
     * @return array<string, mixed> Biểu diễn mảng của thông báo
     */
    public function toArray(object $notifiable): array
    {
        // Đảm bảo lấy avatar URL từ đối tượng liker đã được truyền vào
        // và gán giá trị mặc định nếu nó null hoặc rỗng.
        $likerAvatar = $this->takeAvatarUser($this->liker);

        // Lấy URL ảnh đầu tiên từ JSON hoặc mảng
        $imageUrl = null;
        if (!empty($this->image->image_url)) {
            if (is_string($this->image->image_url)) {
                $decodedUrl = json_decode($this->image->image_url, true);
                $imageUrl = $decodedUrl[0] ?? null;
            } elseif (is_array($this->image->image_url)) {
                $imageUrl = $this->image->image_url[0] ?? null;
            }
        }

        return [
            'interaction_id' => $this->interaction->id,
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'liker_avatar' => $likerAvatar, // Sử dụng biến đã được xử lý
            'image_id' => $this->image->id,
            'image_title' => $this->image->title ?? 'Không có tiêu đề',
            'image_url' => $imageUrl,
            'type' => 'like_image',
            'message' => $this->liker->name . ' đã thích ảnh ' . ($this->image->title ?? 'của bạn') . '.'
        ];
    }

    /**
     * Lấy biểu diễn broadcast của thông báo
     * @param object $notifiable Người nhận thông báo
     * @return BroadcastMessage Biểu diễn broadcast của thông báo
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        // Đảm bảo lấy avatar URL từ đối tượng liker đã được truyền vào
        // và gán giá trị mặc định nếu nó null hoặc rỗng.
        $likerAvatar = $this->takeAvatarUser($this->liker);

        // Lấy URL ảnh đầu tiên từ JSON hoặc mảng
        $imageUrl = null;
        if (!empty($this->image->image_url)) {
            if (is_string($this->image->image_url)) {
                $decodedUrl = json_decode($this->image->image_url, true);
                $imageUrl = $decodedUrl[0] ?? null;
            } elseif (is_array($this->image->image_url)) {
                $imageUrl = $this->image->image_url[0] ?? null;
            }
        }

        return new BroadcastMessage([
            'notification_id' => $this->id,
            'interaction_id' => $this->interaction->id,
            'liker_id' => $this->liker->id,
            'liker_name' => $this->liker->name,
            'liker_avatar' => $likerAvatar, // Sử dụng biến đã được xử lý
            'image_id' => $this->image->id,
            'image_title' => $this->image->title ?? 'Không có tiêu đề',
            'image_url' => $imageUrl,
            'type' => 'like_image',
            'message' => $this->liker->name . ' đã thích ảnh ' . ($this->image->title ?? 'của bạn') . '.',
            'created_at' => now()->toIso8601String(), // Thêm timestamp
            'read_at' => null // Thông báo mới chưa đọc
        ]);
    }

    /**
     * Lấy kiểu broadcast của thông báo
     * @return string Kiểu broadcast của thông báo
     */
    public function broadcastType(): string
    {
        return 'like.image';
    }

    /**
     * Lấy avatar của người thích ảnh
     * @param User $liker Người thích ảnh
     * @return string Avatar của người thích ảnh
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