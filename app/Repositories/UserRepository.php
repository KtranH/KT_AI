<?php

namespace App\Repositories;

use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Kiểm tra trạng thái đăng nhập
     * @return array Kết quả kiểm tra
     */
    public function checkStatus(): array
    {
        return [
            'authenticated' => Auth::check(),
            'user' => Auth::user()
        ];
    }

    /**
     * Tăng số lượng ảnh khi người dùng tải lên
     * @param int $id ID của người dùng
     * @return ?User User
     */
    public function increaseSumImg(int $id): ?User
    {
        /** @var User|null $user */
        $user = User::find($id);
        if ($user) {
            $user->sum_img++;
            $user->save();
            return $user;
        }
        return null;
    }

    /**
     * Tăng số lượng lượt thích khi người dùng thích
     * @param int $userId ID của user
     * @return void
     */
    public function increaseSumLike(int $userId): void
    {
        $user = User::find($userId);
        if ($user) {
            $user->sum_like++;
            $user->save();
        }
    }
    
    /**
     * Giảm số lượng ảnh khi người dùng xóa
     * @param int $id ID của người dùng
     * @return ?User User
     */
    public function decreaseSumImg(int $id): ?User
    {
        /** @var User|null $user */
        $user = User::find($id);
        if ($user && $user->sum_img > 0) {
            $user->sum_img--;
            $user->save();
            return $user;
        }
        return null;
    }

    /**
     * Giảm số lượng lượt thích khi người dùng xóa
     * @param int $userId ID của user
     * @return void
     */
    public function decreaseSumLike(int $userId): void
    {
        $user = User::find($userId);
        if ($user && $user->sum_like > 0) {
            $user->sum_like--;
            $user->save();
        }
    }
    /**
     * Kiểm tra email
     * @param string $email Email cần kiểm tra
     * @return ?User User
     */
    public function checkEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Lấy thông tin người dùng hiện tại
     * @return ?User User
     */
    public function getUser(): ?User
    {
        if (!Auth::check()) {
            return null;
        }
        $userId = Auth::id();
        return User::with(['images', 'comments', 'notifications', 'interactions'])->find($userId) ?? null;
    }

    /**
     * Lấy thông tin người dùng theo ID
     * @param int $id ID của người dùng
     * @return ?User User
     */
    public function getUserById(int $id): ?User
    {
        /** @var User|null $user */
        $user = User::with(['images', 'comments', 'notifications', 'interactions'])->find($id);
        return $user;
    }

    /**
     * Lấy thông tin người dùng theo email
     * @param string $email Email của người dùng
     * @return ?User User
     */
    public function getUserByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = User::where('email', $email)->first();
        return $user;
    }
    /**
     * Lấy danh sách tất cả người dùng
     *
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return User::all();
    }
    /**
     * Tạo người dùng mới
     *
     * @param mixed $request Dữ liệu người dùng
     * @return User
     */
    public function store($request): User
    {
        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar_url' => "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/avatar.png",
            'cover_image_url' => "https://pub-ed515111f589440fb333ebcd308ee890.r2.dev/img/cover_image.png",
            'remaining_credits' => 20,
            'sum_img' => 0,
            'sum_like' => 0,
            'status_user' => 'active',
            'is_verified' => false
        ]);
    }
    /**
     * Cập nhật tên người dùng
     *
     * @param array $activities Mảng hoạt động của người dùng
     * @param User $user Đối tượng người dùng
     * @return User
     */
    public function updateName(array $activities, User $user): User
    {
        // Cập nhật hoạt động
        $user->activities = json_encode($activities);
        $user->last_name_change = Carbon::now()->toDateTimeString();
        $user->save();
        return $user;
    }
    /**
     * Cập nhật trạng thái xác thực email của người dùng
     *
     * @return User|null
     */
    public function updateStatus(): ?User
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $userId = Auth::id();
        $userModel = User::find($userId);
        if (!$userModel) {
            return null;
        }

        $userModel->is_verified = true;

        // Cập nhật hoạt động
        $activities = json_decode($userModel->activities, true) ?? [];
        $activities[] = [
            'action' => 'Xác thực email thành công',
            'timestamp' => Carbon::now()->toDateTimeString(),
        ];
        $userModel->activities = json_encode($activities);

        $userModel->save();
        return $userModel ?? null;
    }

    /**
     * Cập nhật mật khẩu người dùng
     *
     * @param array $activities Mảng hoạt động của người dùng
     * @param User $user Đối tượng người dùng
     * @return User
     */
    public function updatePassword(array $activities, User $user): User
    {
        $user->activities = json_encode($activities);
        $user->last_password_change = Carbon::now()->toDateTimeString();
        $user->save();
        return $user;
    }

    /**
     * Cập nhật avatar người dùng
     *
     * @param string $path Đường dẫn avatar mới
     * @return User
     */
    public function updateAvatar(string $path): User
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('User not authenticated');
        }

        $userId = Auth::id();
        $userModel = User::findOrFail($userId);

        $userModel->avatar_url = $path;

        // Cập nhật hoạt động
        $activities = json_decode($userModel->activities, true) ?? [];
        $activities[] = [
            'action' => 'Cập nhật avatar',
            'timestamp' => Carbon::now()->toDateTimeString(),
        ];
        $userModel->activities = json_encode($activities);

        $userModel->save();
        return $userModel;
    }

    /**
     * Cập nhật ảnh bìa người dùng
     *
     * @param string $path Đường dẫn ảnh bìa mới
     * @return User
     */
    public function updateCoverImage(string $path): User
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('User not authenticated');
        }

        $userId = Auth::id();
        $userModel = User::findOrFail($userId);

        $userModel->cover_image_url = $path;

        // Cập nhật hoạt động
        $activities = json_decode($userModel->activities, true) ?? [];
        $activities[] = [
            'action' => 'Cập nhật ảnh bìa',
            'timestamp' => Carbon::now()->toDateTimeString(),
        ];
        $userModel->activities = json_encode($activities);

        $userModel->save();
        return $userModel;
    }

    /**
     * Xóa người dùng
     *
     * @param int $id ID của người dùng
     * @return User
     */
    public function delete(int $id): User
    {
        $user = User::findOrFail($id);
        $user->delete();
        return $user;
    }
    /**
     * Kiểm tra mật khẩu người dùng
     *
     * @param string $current_password Mật khẩu hiện tại
     * @return bool
     */
    public function checkPassword(string $current_password): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $userId = Auth::id();
        $userModel = User::find($userId);
        if (!$userModel) {
            return false;
        }

        return Hash::check($current_password, $userModel->password);
    }

    /**
     * Kiểm tra số lượng ảnh của người dùng
     *
     * @param int $id ID của người dùng
     * @return int
     */
    public function checkSumImg(int $id): int
    {
        $user = User::find($id);
        if (!$user) {
            return 0;
        }
        return $user->sum_img;
    }

    /**
     * Tìm user theo ID (có thể null)
     */
    public function findById(int $userId): ?User
    {
        return User::find($userId);
    }
    
    /**
     * Lấy user với thông tin cần thiết cho notification
     */
    public function getUserForNotification(int $userId): ?User
    {
        return User::select('id', 'name', 'avatar_url')->find($userId);
    }
}
