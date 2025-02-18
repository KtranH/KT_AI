<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function check()
    {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::user()
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar_url' => "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png",
            'cover_image_url' => "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover_image.png",
            'remaining_credits' => 0,
            'sum_img' => 0,
            'sum_like' => 0,
            'status_user' => 'active'
        ]);

        Auth::login($user);

        return response()->json([
            'message' => 'Đăng ký thành công',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['Thông tin đăng nhập không chính xác.'],
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => Auth::user()
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Đã đăng xuất thành công']);
    }

    public function user(Request $request)
    {
        return response()->json(Auth::user());
    }
} 