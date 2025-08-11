<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Exceptions\ExternalServiceException;
use App\Models\User;
use App\Interfaces\UserRepositoryInterface;
use App\Http\Resources\V1\Auth\AuthResource;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleService extends BaseService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Tạo URL redirect cho Google OAuth
     * 
     * @return array
     * @throws Exception
     */
    public function getRedirectUrl(): array
    {
        return $this->executeWithExceptionHandling(function() {
            $redirectUrl = Socialite::driver('google')->redirect()->getTargetUrl();
            $this->logAction('Google OAuth redirect URL generated');
            return [
                'url' => $redirectUrl
            ];
        }, 'Generating Google OAuth redirect URL');
    }

    /**
     * Xử lý callback từ Google OAuth
     * 
     * @return array
     * @throws Exception
     */
    public function handleCallback(): array
    {
        return $this->executeInTransactionSafely(function () {
            $googleUser = Socialite::driver('google')->user();
            
            if (!$googleUser || !$googleUser->email) {
                throw new ExternalServiceException(
                    'Lỗi từ Google OAuth: Không nhận được dữ liệu user hợp lệ',
                    ['service' => 'Google OAuth', 'issue' => 'Invalid user data received']
                );
            }
            
            // Tìm hoặc tạo user
            $user = $this->findOrCreateUser($googleUser);
            
            // Đăng nhập user với remember me (Google OAuth thường được coi là "trusted")
            Auth::login($user, true); // true = remember me
            
            // Đảm bảo session được tạo và regenerate để tăng bảo mật
            session()->regenerate();
            
            $this->logAction('Google OAuth login successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'remember' => true
            ]);
            
            // Tạo response giống như web login: session-based, không có token
            $authResource = new AuthResource(
                $user,
                null, // Không có token cho web session
                'Session',
                true, // Google OAuth = remember me
                null, // Không có expiration cho session
                'Đăng nhập Google thành công (session cookie)'
            );

            return $authResource->toArray(request());
        }, 'Processing Google OAuth callback');
    }

    /**
     * Tìm hoặc tạo user từ Google user data
     * 
     * @param object $googleUser
     * @return User
     */
    private function findOrCreateUser(object $googleUser): User
    {
        $existingUser = $this->userRepository->getUserByEmail($googleUser->email);
        
        if ($existingUser) {
            // Cập nhật thông tin nếu cần
            if (!$existingUser->is_verified) {
                $existingUser->is_verified = true;
                $existingUser->save();
            }
            
            return $existingUser;
        }
        
        // Tạo user mới - tạo object từ data
        $userData = (object) [
            'email' => $googleUser->email,
            'name' => $googleUser->name ?? 'User123',
            'password' => uniqid(), // sẽ được hash trong store method
            'avatar_url' => $googleUser->avatar ?? 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
            'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover_image.png',
            'is_verified' => true
        ];
        
        $newUser = $this->userRepository->store($userData);
        
        // Cập nhật is_verified sau khi tạo (vì store method mặc định là false)
        $newUser->is_verified = true;
        $newUser->save();
        
        return $newUser;
    }

    /**
     * Tạo response JavaScript cho popup window
     * 
     * @param array $data
     * @return string
     */
    public function createPopupResponse(array $data): string
    {
        $jsonData = json_encode($data);
        $appUrl = config('app.url', 'http://localhost:8000');
        
        return "
            <script>
                try {
                    // Gửi message với origin cụ thể thay vì wildcard để bảo mật
                    if (window.opener) {
                        window.opener.postMessage({$jsonData}, '{$appUrl}');
                    }
                } catch (error) {
                    console.error('Lỗi khi gửi message:', error);
                }
                
                try {
                    // Thử đóng popup, bỏ qua lỗi Cross-Origin-Opener-Policy
                    window.close();
                } catch (error) {
                    // Popup sẽ tự đóng hoặc user đóng thủ công
                    console.log('Popup sẽ tự đóng');
                }
            </script>
        ";
    }
} 