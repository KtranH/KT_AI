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
        return Socialite::driver('google')->redirect();
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
            
            return redirect('/dashboard')->with('success', 'Đăng nhập thành công!');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Đăng nhập bằng Google thất bại. Vui lòng thử lại.');
        }
    }
} 