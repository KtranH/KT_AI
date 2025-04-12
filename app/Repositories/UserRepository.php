<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class UserRepository implements UserRepositoryInterface
{
    // Kiểm tra trạng thái đăng nhập 
    public function checkStatus()
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
                return response()->json([
                    'authenticated' => true,
                    'user' => $user
                ]);
            }
            
            return response()->json([
                'authenticated' => false
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    // Tăng số lượng ảnh tải lên của tài khoản
    public function increaseSumImg($id)
    {
        $user = User::find($id);
        $user->sum_img++;
        $user->save();
        return $user;
    }
    // Giảm số lượng ảnh tải lên của tài khoản
    public function decreaseSumImg($id)
    {
        $user = User::find($id);
        $user->sum_img--;
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

    // Lấy user theo ID
    public function getUserById($id)
    {
        $user = User::with(['images', 'comments', 'notifications', 'interactions'])->find($id);
        return $user;
    }

    // Lấy user theo Email
    public function getUserByEmail($email)
    {
        $user = User::where('email', $email)->first();
        return $user;
    }

    // Lấy danh sách user
    public function getUsers(): Collection
    {
        return User::all();
    }

    // Tạo user
    public function store($request)
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

    // Cập nhật thông tin user
    public function update($request, $id)
    {
        $user = User::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();
        return $user;
    }

    // Cập nhật mật khẩu
    public function updatePassword($request, $id)
    {
        $user = User::find($id);
        $user->password = Hash::make($request->password);
        $user->save();
        return $user;
    }

    // Xóa user
    public function delete($id)
    {
        $user = User::find($id);
        $user->delete();
        return $user;
    }

    // Kiểm tra mật khẩu
    public function checkPassword($request, $id)
    {
        $user = User::find($id);
        return Hash::check($request->password, $user->password);
    }

    // Kiểm tra số lượng ảnh
    public function checkSumImg($id)
    {
        $user = User::find($id);
        return $user->sum_img;
    }

    // Cập nhật trạng thái email của user
    public function updateUserStatus($id)
    {
        $user = User::find($id);
        $user->is_verified = !$user->is_verified;
        $user->save();
        return $user;
    }
}
