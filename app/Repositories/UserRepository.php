<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\JsonResponse;

class UserRepository implements UserRepositoryInterface
{
    public function checkStatus(): JsonResponse
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
    public function increaseSumImg($id): User
    {
        $user = User::find($id);
        $user->sum_img++;
        $user->save();
        return $user;
    }
    public function increaseSumLike(User $user): void
    {
        $user->sum_like++;
        $user->save();
    }
    public function decreaseSumImg($id): User
    {
        $user = User::find($id);
        $user->sum_img--;
        $user->save();
        return $user;
    }
    public function decreaseSumLike(User $user): void
    {
        $user->sum_like--;
        $user->save();
    }
    public function checkEmail($email): User
    {
        $user = User::where('email', $email)->first();
        return $user;
    }
    public function getUser(): User
    {
        $user = User::with(['images', 'comments', 'notifications', 'interactions'])->find(Auth::user()->id);
        return $user;
    }
    public function getUserById($id): User
    {
        $user = User::with(['images', 'comments', 'notifications', 'interactions'])->find($id);
        return $user;
    }
    public function getUserByEmail($email): User
    {
        $user = User::where('email', $email)->first();
        return $user;
    }
    public function getUsers(): Collection
    {
        return User::all();
    }
    public function store($request): User
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
    public function updateName($request): User
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
    public function updateStatus(): User
    {
        $user = Auth::user();
        $user->is_verified = !$user->is_verified;
        $user->save();
        return $user;
    }
    public function updatePassword($request, $user = null): User
    {
        if (!$user) {
            $user = Auth::user();
        }
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
    public function updateAvatar($path): User
    {
        $user = Auth::user();
        $user->avatar_url = $path;
        $user->save();
        return $user;
    }
    public function updateCoverImage($path): User
    {
        $user = Auth::user();
        $user->cover_image_url = $path;
        $user->save();
        return $user;
    }
    public function delete($id): User
    {
        $user = User::find($id);
        $user->delete();
        return $user;
    }
    public function checkPassword($current_password): bool
    {
        $user = Auth::user();
        return Hash::check($current_password, $user->password);
    }
    public function checkSumImg($id): int
    {
        $user = User::find($id);
        return $user->sum_img;
    }
}
