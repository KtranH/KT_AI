<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Mail\VerificationMail;
use Exception;

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

    public function sendPasswordChangeVerification($user)
    {
        try
        {
            $verificationcode = Str::random(6);
            Redis::setex("password_change_code:{$user->email}", 600, $verificationcode);
            $detail = [
                'title' => "Mã xác nhận đổi mật khẩu từ KT-AI",
                'begin' => "Xin chào " . $user->name,
                'body' => "Chúng tôi nhận được yêu cầu đổi mật khẩu từ tài khoản của bạn. Nhập mã dưới đây để xác nhận việc đổi mật khẩu (Lưu ý mã chỉ có khả dụng trong 10 phút)",
                'code' => $verificationcode
            ];
            Mail::to($user->email)->send(new VerificationMail($detail));
            return true;
        }
        catch (Exception $e)
        {
            Log::error('Lỗi khi gửi email xác nhận đổi mật khẩu: ' . $e->getMessage());
            return false;
        }
    }
}
