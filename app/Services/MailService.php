<?php

namespace App\Services;

class MailService
{
    public function sendMail($user)
    {
        try
        {
            $verificationcode = Str::random(6);
            Redis::setex("verify_code:{$user->email}", 600, $verificationcode);
            $detail = [
                'title' => "Mã xác nhận từ KT-AI",
                'begin' => "Xin chào " . $user->name,
                'body' => "Chúng tôi nhận được lượt đăng ký tài khoản của bạn. Nhập mã dưới đây để hoàn tất xác minh (Lưu ý mã chỉ có khả dụng trong 10 phút)",
                'code' => $verificationcode
            ];
            Mail::to($user->email)->send(new VerificationMail($detail));
            return true;
        }
        catch (Exception $e)
        {
            Log::error('Lỗi khi gửi email xác nhận: ' . $e->getMessage());
            return false;
        }
    }
    public function sendMailResend($user)
    {
        try
        {
            $verificationcode = Str::random(6);
            Redis::setex("verify_code:{$user->email}", 600, $verificationcode);
            $detail = [
                'title' => "Mã xác nhận từ KT-AI",
                'begin' => "Xin chào " . $user->name,
                'body' => "Chúng tôi nhận được yêu cầu gửi lại mã xác thực. Nhập mã dưới đây để hoàn tất xác minh (Lưu ý mã chỉ có khả dụng trong 10 phút)",
                'code' => $verificationcode
            ];
            Mail::to($user->email)->send(new VerificationMail($detail));
            return true;
        }
        catch (Exception $e)
        {
            Log::error('Lỗi khi gửi email xác nhận: ' . $e->getMessage());
            return false;
        }
    }
}
