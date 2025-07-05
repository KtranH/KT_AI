<?php

namespace App\Interfaces;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface UserRepositoryInterface
{
    /**
     * Kiểm tra trạng thái đăng nhập
     * @return array Kết quả kiểm tra
     */
    public function checkStatus(): array;



    /**
     * Kiểm tra email
     * @param string $email Email của user
     * @return ?User User
     */
    public function checkEmail(string $email): ?User;

    /**
     * Tạo user
     * @param Request $request Yêu cầu tạo user
     * @return User User
     */
    public function store(Request $request): User;

    /**
     * Xóa user
     * @param int $id ID của user
     * @return User User
     */
    public function delete(int $id): User;

    /**
     * Tăng số lượng ảnh khi người dùng tải lên
     * @param int $id ID của user
     * @return ?User User
     */
    public function increaseSumImg(int $id): ?User;

    /**
     * Tăng số lượng lượt thích khi người dùng thích
     * @param int $userId ID của user
     * @return void
     */
    public function increaseSumLike(int $userId): void;

    /**
     * Giảm số lượng ảnh khi người dùng xóa
     * @param int $id ID của user
     * @return ?User User
     */
    public function decreaseSumImg(int $id): ?User;

    /**
     * Giảm số lượng lượt thích khi người dùng xóa
     * @param int $userId ID của user
     * @return void
     */
    public function decreaseSumLike(int $userId): void;

    /**
     * Lấy user hiện tại
     * @return ?User User
     */
    public function getUser(): ?User;

    /**
     * Lấy danh sách user
     * @return Collection Danh sách user
     */
    public function getUsers(): Collection;

    /**
     * Lấy thông tin user theo ID
     * @param int $id ID của user
     * @return ?User User
     */
    public function getUserById(int $id): ?User;

    /**
     * Lấy thông tin user theo Email
     * @param string $email Email của user
     * @return ?User User
     */
    public function getUserByEmail(string $email): ?User;

    /**
     * Cập nhật tên user
     * @param array $activities Hoạt động của user
     * @param User $user User
     * @return User User
     */
    public function updateName(array $activities, User $user): User;

    /**
     * Cập nhật mật khẩu user
     * @param array $activities Hoạt động của user
     * @param User $user User
     * @return User User
     */
    public function updatePassword(array $activities, User $user): User;

    /**
     * Cập nhật avatar
     * @param string $path Đường dẫn của avatar
     * @return User User
     */
    public function updateAvatar(string $path): User;

    /**
     * Cập nhật cover image
     * @param string $path Đường dẫn của cover image
     * @return User User
     */
    public function updateCoverImage(string $path): User;

    /**
     * Cập nhật trạng thái email của user
     * @return ?User User
     */
    public function updateStatus(): ?User;

    /**
     * Kiểm tra mật khẩu user
     * @param string $current_password Mật khẩu hiện tại
     * @return bool Kết quả kiểm tra
     */
    public function checkPassword(string $current_password): bool;

    /**
     * Kiểm tra số lượng ảnh user
     * @param int $id ID của user
     * @return int Số lượng ảnh user
     */
    public function checkSumImg(int $id): int;

    /**
     * Tìm user theo ID (có thể null)
     */
    public function findById(int $userId): ?User;
    
    /**
     * Lấy user với thông tin cần thiết cho notification
     */
    public function getUserForNotification(int $userId): ?User;
}
