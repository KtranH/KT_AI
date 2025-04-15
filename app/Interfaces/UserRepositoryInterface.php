<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    // Kiểm tra email
    public function checkEmail($email);

    // Tạo user
    public function store($request);

    // Xóa user
    public function delete($id);

    // Tăng số lượng ảnh khi người dùng tải lên
    public function increaseSumImg($id);

    // Lấy user hiện tại
    public function getUser();

    // Lấy danh sách user
    public function getUsers(): Collection;

    // Lấy thông tin user theo ID
    public function getUserById($id);

    // Lấy thông tin user theo Email
    public function getUserByEmail($email);
    
    // Cập nhật tên user
    public function updateName($request);
    
    // Cập nhật mật khẩu user
    public function updatePassword($request);
    
    // Cập nhật avatar
    public function updateAvatar($path);

    // Cập nhật cover image
    public function updateCoverImage($path);

    // Cập nhật trạng thái email của user
    public function updateStatus();

    // Kiểm tra mật khẩu user
    public function checkPassword($request, $id);

    // Kiểm tra số lượng ảnh user
    public function checkSumImg($id);

    // Kiểm tra trạng thái đăng nhập
    public function checkStatus();
}
