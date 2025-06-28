<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirectUrl()
    {
        try {
            $redirectUrl = Socialite::driver('google')->redirect()->getTargetUrl();
            
            return response()->json(['url' => $redirectUrl]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Không thể tạo URL đăng nhập Google',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function handleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'password' => Hash::make(uniqid()),
                    'is_verified' => true
                ] + (User::where('email', $googleUser->email)->exists() ? [] : ['avatar_url' => $googleUser->avatar ?? 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png', 'name' => $googleUser->name ?? 'User123', 'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover_image.png',])
            );
            
            Auth::login($user);
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return "
            <script>
                window.opener.postMessage({
                    success: true,
                    message: 'Đăng nhập thành công',
                    user: " . json_encode($user) . ",
                    token: '" . $token . "'
                }, '*');
                window.close();
            </script>
        ";
        } catch (\Exception $e) {
            return "
                <script>
                    window.opener.postMessage({
                        success: false,
                        message: 'Đăng nhập thất bại',
                        error: '" . $e->getMessage() . "'
                    }, '*');
                    window.close();
                </script>
            ";
        }
    }
} 