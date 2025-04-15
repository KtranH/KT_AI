<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

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
    public function updateName($request)
    {
        $user = Auth::user();
        $user->name = $request->name;
        $activities = json_decode($user->activities, true) ?? [];
        $activities[] = [
            'action' => 'Cập nhật tên tài khoản thành ' . $user->name,
            'timestamp' => now()
        ];
        $user->activities = json_encode($activities);
        $user->save();
        return $user;
    }

    // Cập nhật trạng thái email của user
    public function updateStatus()
    {
        $user = Auth::user();
        $user->is_verified = !$user->is_verified;
        $user->save();
        return $user;
    }

    // Cập nhật mật khẩu
    public function updatePassword($request)
    {
        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $activities = json_decode($user->activities, true) ?? [];
        $activities[] = [
            'action' => 'Cập nhật mật khẩu',
            'timestamp' => now()
        ];
        $user->activities = json_encode($activities);
    $user->save();
    return $user;
}
    // Cập nhật avatar
    public function updateAvatar($path)
    {
        $user = Auth::user();
        $user->avatar_url = $path;
        $user->save();
        return $user;
    }

    // Cập nhật cover image
    public function updateCoverImage($path)
    {
        $user = Auth::user();
        $user->cover_image_url = $path;
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
    public function checkPassword($current_password)
    {
        $user = Auth::user();
        return Hash::check($current_password, $user->password);
    }

    // Kiểm tra số lượng ảnh
    public function checkSumImg($id)
    {
        $user = User::find($id);
        return $user->sum_img;
    }
}
