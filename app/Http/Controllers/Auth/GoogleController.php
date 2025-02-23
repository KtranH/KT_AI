<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function redirectUrl()
    {
        $redirectUrl = Socialite::driver('google')->redirect()->getTargetUrl();
        return response()->json(['url' => $redirectUrl]);
    }
    public function handleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'password' => Hash::make(uniqid()),
                    'avatar_url' => $googleUser->avatar ?? 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png',
                    'cover_image_url' => 'https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover_image.png',
                    'sum_like' => 0,
                    'sum_img' => 0,
                    'remaining_creadits' => 20,
                    'status_user' => 'active',
                    'is_verified' => true
                ]
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