<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    // Tăng số lượng ảnh tải lên của tài khoản
    public function increaseSumImg($id)
    {
        $user = User::find($id);
        $user->sum_img++;
        $user->save();
        return $user;
    }
    // Kiểm tra email của tài khoản
    public function checkEmail($email)
    {
        $user = User::where('email', $email)->first();
        return $user;
    }
    // Lấy user mới nhất từ database
    public function getUser()
    {
        $user = User::with(['images', 'comments', 'notifications', 'interactions'])->find(Auth::user()->id);
        return $user;
    }
    public function createUser($request)
    {
        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar_url' => "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png",
            'cover_image_url' => "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover_image.png",
            'remaining_creadits' => 0,
            'sum_img' => 0,
            'sum_like' => 0,
            'status_user' => 'active',
            'is_verified' => false
        ]);
    }

    public function updateUser($request, $id)
    {
        $user = User::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        return $user;
    }

    public function updateUserPassword($request, $id)
    {
        $user = User::find($id);
        $user->password = Hash::make($request->password);
        $user->save();
        return $user;
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        $user->delete();
        return $user;
    }
}
