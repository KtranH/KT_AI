<?php

namespace App\Interfaces;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;

interface UserRepositoryInterface
{
    // Kiểm tra trạng thái đăng nhập
    public function checkStatus(): JsonResponse;

    // Kiểm tra credits còn lại
    public function checkCredits(): JsonResponse;

    // Kiểm tra email
    public function checkEmail($email): ?User;

    // Tạo user
    public function store($request): User;

    // Xóa user
    public function delete($id): User;

    // Tăng số lượng ảnh khi người dùng tải lên
    public function increaseSumImg($id): ?User;

    // Tăng số lượng lượt thích khi người dùng thích
    public function increaseSumLike(User $user): void;

    // Giảm số lượng ảnh khi người dùng xóa
    public function decreaseSumImg($id): ?User;

    // Giảm số lượng lượt thích khi người dùng xóa
    public function decreaseSumLike(User $user): void;

    // Lấy user hiện tại
    public function getUser(): ?User;

    // Lấy danh sách user
    public function getUsers(): Collection;

    // Lấy thông tin user theo ID
    public function getUserById($id): ?User;

    // Lấy thông tin user theo Email
    public function getUserByEmail($email): ?User;

    // Cập nhật tên user
    public function updateName($activities, $user): User;

    // Cập nhật mật khẩu user
    public function updatePassword($activities, $user): User;

    // Cập nhật avatar
    public function updateAvatar($path): User;

    // Cập nhật cover image
    public function updateCoverImage($path): User;

    // Cập nhật trạng thái email của user
    public function updateStatus(): ?User;

    // Kiểm tra mật khẩu user
    public function checkPassword($current_password): bool;

    // Kiểm tra số lượng ảnh user
    public function checkSumImg($id): int;
}
