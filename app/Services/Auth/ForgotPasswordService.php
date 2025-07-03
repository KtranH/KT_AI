<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Exceptions\BusinessException;
use App\Exceptions\ExternalServiceException;
use App\Interfaces\UserRepositoryInterface;
use App\Services\Auth\TurnStileService;
use App\Services\External\MailService;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRules;
use Illuminate\Auth\Events\PasswordReset;
use App\Http\Resources\PasswordResetResource;

class ForgotPasswordService extends BaseService
{
    /**
     * The UserRepositoryInterface instance.
     *
     * @var UserRepositoryInterface
     */
    protected UserRepositoryInterface $userRepository;

    /**
     * The TurnStileService instance.
     *
     * @var TurnStileService
     */
    protected TurnStileService $turnStileService;

    /**
     * The MailService instance.
     *
     * @var MailService
     */
    protected MailService $mailService;

    /**
     * Create a new service instance.
     *
     * @param UserRepositoryInterface $userRepository
     * @param TurnStileService $turnStileService
     * @param MailService $mailService
     * @return void
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        TurnStileService $turnStileService,
        MailService $mailService
    ) {
        $this->userRepository = $userRepository;
        $this->turnStileService = $turnStileService;
        $this->mailService = $mailService;
    }

    /**
     * Gửi email chứa mã xác nhận khôi phục mật khẩu
     */
    public function sendResetLinkEmail(Request $request): array
    {
        return $this->executeWithExceptionHandling(function() use ($request) {
            $request->validate([
                'email' => 'required|email',
                'cf-turnstile-response' => 'required|string'
            ]);

            // Xác thực Turnstile
            $turnstileResponse = $this->turnStileService->verifyTurnstile(
                $request->input('cf-turnstile-response'),
                $request->ip()
            );

            if (!$turnstileResponse['success']) {
                throw new BusinessException('Xác thực captcha không thành công. Vui lòng thử lại.');
            }

            // Kiểm tra rate limiting
            $this->checkRateLimit($request->email);

            // Tạo và lưu mã xác thực
            $verificationCode = $this->generateAndStoreVerificationCode($request->email);

            // Gửi email chứa mã xác thực
            $user = $this->userRepository->getUserByEmail($request->email);

            if ($user) {
                $user->sendPasswordResetNotification($verificationCode);
                return PasswordResetResource::sendCode($request->email, 'Chúng tôi đã gửi mã xác nhận đến email của bạn.');
            }

            return PasswordResetResource::sendCode($request->email, 'Chúng tôi đã gửi mã xác nhận đến email của bạn nếu tài khoản tồn tại.');
        }, "Sending password reset email to: {$request->email}");
    }

    /**
     * Kiểm tra rate limiting cho email
     */
    protected function checkRateLimit(string $email): void
    {
        $emailKey = "password_reset_code_send_count:{$email}";
        $attempts = Redis::incr($emailKey);
        if ($attempts === 1) {
            Redis::expire($emailKey, 300); // 5 phút
        }

        if ($attempts > 3) {
            $ttl = Redis::ttl($emailKey);
            throw BusinessException::quotaExceeded("yêu cầu reset password", 3);
        }
    }

    /**
     * Tạo và lưu mã xác thực
     */
    protected function generateAndStoreVerificationCode(string $email): string
    {
        $verificationCode = rand(100000, 999999);
        $verificationCodeStr = strval($verificationCode);

        // Lưu mã vào Redis (thời hạn 10 phút)
        Redis::setex("password_reset_code:{$email}", 600, Hash::make($verificationCodeStr));
        // Lưu trữ thời gian tạo mã
        Redis::setex("password_reset_code_created_at:{$email}", 600, time());

        return $verificationCodeStr;
    }

    /**
     * Xác thực mã xác nhận qua email
     */
    public function verifyCode(Request $request): array
    {
        return $this->executeWithExceptionHandling(function() use ($request) {
            $request->validate([
                'email' => 'required|email',
                'code' => 'required|string|size:6',
            ]);

            $record = Redis::get("password_reset_code:{$request->email}");
            $createdAtTimeStamp = Redis::get("password_reset_code_created_at:{$request->email}");

            if (!$record) {
                throw new BusinessException('Mã xác thực không hợp lệ hoặc đã hết hạn');
            }

            // Kiểm tra thời gian tạo mã (mã chỉ có hiệu lực trong 60 phút)
            if ($createdAtTimeStamp && (time() - (int)$createdAtTimeStamp) > 3600) {
                Redis::del("password_reset_code:{$request->email}");
                Redis::del("password_reset_code_created_at:{$request->email}");
                throw new BusinessException('Mã xác thực đã hết hạn. Vui lòng yêu cầu gửi lại mã mới.');
            }

            // Kiểm tra mã xác thực
            if (!Hash::check($request->code, $record)) {
                throw new BusinessException('Mã xác thực không chính xác.');
            }

            // Tạo token mới để sử dụng cho bước reset mật khẩu
            $token = Str::random(64);
            Redis::setex("password_reset_token:{$request->email}", 600, $token);

            return PasswordResetResource::verifyCodeSuccess($request->email, $token, 'Mã xác thực hợp lệ');
        }, "Verifying password reset code for email: {$request->email}");
    }

    /**
     * Đặt lại mật khẩu
     */
    public function reset(Request $request): array
    {
        return $this->executeInTransactionSafely(function() use ($request) {
            $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
                'password' => ['required', 'confirmed', PasswordRules::defaults()],
            ]);

            // Kiểm tra token
            $token = Redis::get("password_reset_token:{$request->email}");
            if (!$token || $token !== $request->token) {
                throw new BusinessException('Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.');
            }

            // Kiểm tra user tồn tại
            $user = $this->userRepository->getUserByEmail($request->email);
            if (!$user) {
                throw BusinessException::resourceNotFound('tài khoản', $request->email);
            }

            // Cập nhật mật khẩu trong transaction
            $this->updatePasswordInTransaction($user, $request->password, $request->email);

            return PasswordResetResource::resetSuccess($request->email, 'Mật khẩu đã được đặt lại thành công');
        }, "Resetting password for email: {$request->email}");
    }

    /**
     * Cập nhật mật khẩu trong transaction
     */
    protected function updatePasswordInTransaction($user, string $password, string $email): void
    {
        $this->executeInTransactionSafely(function() use ($user, $password, $email) {
            // Cập nhật mật khẩu
            $activities = [
                [
                    'action' => 'Đặt lại mật khẩu',
                    'timestamp' => time()
                ]
            ];

            $user->password = $password;
            $user->save();

            // Cập nhật hoạt động
            $this->userRepository->updatePassword($activities, $user);

            // Xóa tất cả các khóa Redis liên quan
            Redis::del("password_reset_token:{$email}");
            Redis::del("password_reset_code:{$email}");
            Redis::del("password_reset_code_created_at:{$email}");
            Redis::del("password_reset_code_send_count:{$email}");

            // Kích hoạt sự kiện password reset
            event(new PasswordReset($user));
        }, "Updating password for user ID: {$user->id}");
    }
}