<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    // Kiểm tra email
    public function checkEmail($email);

    // Tạo user
    public function store($request);

    // Cập nhật user
    public function update($request, $id);

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

    // Cập nhật mật khẩu user
    public function updatePassword($request, $id);

    // Kiểm tra mật khẩu user
    public function checkPassword($request, $id);

    // Kiểm tra số lượng ảnh user
    public function checkSumImg($id);

    // Kiểm tra trạng thái đăng nhập
    public function checkStatus();
}
