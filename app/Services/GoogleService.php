<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
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
        try {
            $redirectUrl = Socialite::driver('google')->redirect()->getTargetUrl();
            
            $this->logAction('Google OAuth redirect URL generated');
            
            return [
                'url' => $redirectUrl
            ];
        } catch (Exception $e) {
            $this->logAction('Failed to generate Google OAuth redirect URL', [
                'error' => $e->getMessage()
            ]);
            
            throw new Exception('Không thể tạo URL đăng nhập Google: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý callback từ Google OAuth
     * 
     * @return array
     * @throws Exception
     */
    public function handleCallback(): array
    {
        return $this->executeInTransaction(function () {
            try {
                $googleUser = Socialite::driver('google')->user();
                
                // Tìm hoặc tạo user
                $user = $this->findOrCreateUser($googleUser);
                
                // Đăng nhập user
                Auth::login($user);
                
                // Tạo token
                $token = $user->createToken('google_auth_token')->plainTextToken;
                
                $this->logAction('Google OAuth login successful', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Đăng nhập thành công',
                    'user' => $user,
                    'token' => $token
                ];
                
            } catch (Exception $e) {
                $this->logAction('Google OAuth callback failed', [
                    'error' => $e->getMessage()
                ]);
                
                throw new Exception('Đăng nhập thất bại: ' . $e->getMessage());
            }
        });
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
        
        return "
            <script>
                window.opener.postMessage({$jsonData}, '*');
                window.close();
            </script>
        ";
    }
} 