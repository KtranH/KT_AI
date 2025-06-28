<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Mã xác thực
     * @var string Mã xác thực
     * @var string
     */
    private string $code;

    /**
     * Tạo instance thông báo mới
     * @param string $code Mã xác thực
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * Lấy kênh gửi thông báo
     * @param object $notifiable Người nhận thông báo
     * @return array<int, string> Danh sách kênh gửi thông báo
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Lấy nội dung email thông báo
     * @param object $notifiable Người nhận thông báo
     * @return MailMessage Biểu diễn email của thông báo
     */
    public function toMail($notifiable): MailMessage
    {
        $codeHtml = $this->formatVerificationCode($this->code);
        
        return (new MailMessage)
            ->subject('Đặt lại mật khẩu')
            ->greeting('Xin chào!')
            ->line('Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.')
            ->line(new HtmlString('Đây là mã xác nhận của bạn:'))
            ->line(new HtmlString('<div style="text-align: center; margin: 25px 0;">' . $codeHtml . '</div>'))
            ->line('Mã này sẽ hết hạn sau 60 phút.')
            ->line('Nếu bạn không yêu cầu đặt lại mật khẩu, bạn có thể bỏ qua email này.')
            ->salutation(new HtmlString('Trân trọng,<br>'.config('app.name')));
    }

    /**
     * Format mã xác thực để hiển thị trong email
     * @param string $code Mã xác thực
     * @return string Mã xác thực định dạng
     */
    private function formatVerificationCode(string $code): string
    {
        $html = '';
        foreach (str_split($code) as $digit) {
            $html .= sprintf(
                '<span style="display: inline-block; border: 1px solid #ccc; padding: 10px 15px; margin: 0 5px; border-radius: 5px; font-size: 24px; font-weight: bold; background-color: #f8f8f8;">%s</span>',
                $digit
            );
        }
        return $html;
    }
} 