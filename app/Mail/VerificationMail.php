<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Tạo mới một thông báo xác thực
     * @param array $detail Chi tiết thông báo
     */
    public $detail;
    public function __construct($detail)
    {
        //
        $this->detail = $detail;
    }

    /**
     * Lấy biểu diễn envelope của thông báo
     * @return Envelope Biểu diễn envelope của thông báo
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nơi chia sẻ và sáng tạo hình ảnh',
        );
    }

    /**
     * Lấy biểu diễn nội dung của thông báo
     * @return Content Biểu diễn nội dung của thông báo
     */
    public function content(): Content
    {
        return new Content(
            view: 'Mail.VerificateCode',
        );
    }

    /**
     * Lấy các đính kèm của thông báo
     * @return array<int, \Illuminate\Mail\Mailables\Attachment> Danh sách các đính kèm của thông báo
     */
    public function attachments(): array
    {
        return [];
    }
}
