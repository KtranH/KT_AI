<?php

namespace App\Http\Controllers;

class SuccessMessages
{
    // Generic success messages
    const SUCCESS_UPDATE = 'Cập nhật thành công';
    const SUCCESS_UPLOAD = 'Tải lên thành công';
    const SUCCESS_DELETE = 'Xóa thành công';
    const SUCCESS_CREATE = 'Tạo thành công';
    
    // User related success messages
    const USER_UPDATE_SUCCESS = 'Cập nhật thông tin người dùng thành công';
    const USER_CREATE_SUCCESS = 'Tạo tài khoản thành công';
    
    // Image related success messages
    const IMAGE_UPLOAD_SUCCESS = 'Tải lên hình ảnh thành công';
    const IMAGE_UPDATE_SUCCESS = 'Cập nhật hình ảnh thành công';
    const IMAGE_DELETE_SUCCESS = 'Xóa hình ảnh thành công';
    const IMAGE_DOWNLOAD_SUCCESS = 'Tải hình ảnh thành công';
    
    // Comment related success messages
    const COMMENT_CREATE_SUCCESS = 'Tạo bình luận thành công';
    const COMMENT_UPDATE_SUCCESS = 'Cập nhật bình luận thành công';
    const COMMENT_DELETE_SUCCESS = 'Xóa bình luận thành công';
    const REPLY_CREATE_SUCCESS = 'Tạo phản hồi thành công';
    const LIKE_TOGGLE_SUCCESS = 'Thay đổi trạng thái thích thành công';
    
    // Auth related success messages
    const LOGIN_SUCCESS = 'Đăng nhập thành công';
    const LOGOUT_SUCCESS = 'Đăng xuất thành công';
    const REGISTER_SUCCESS = 'Đăng ký thành công';
    const PASSWORD_RESET_SUCCESS = 'Đặt lại mật khẩu thành công';
    const EMAIL_VERIFIED_SUCCESS = 'Xác thực email thành công';

    // Image Job related success messages
    const IMAGE_JOB_CREATE_SUCCESS = 'Tạo tiến trình thành công';
    const IMAGE_JOB_UPDATE_SUCCESS = 'Cập nhật tiến trình thành công';
    const IMAGE_JOB_DELETE_SUCCESS = 'Xóa tiến trình thành công';
    const IMAGE_JOB_RETRY_SUCCESS = 'Thử lại tiến trình thành công';
    const IMAGE_JOB_CANCEL_SUCCESS = 'Hủy tiến trình thành công';
}
