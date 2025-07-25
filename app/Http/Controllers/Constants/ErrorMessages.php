<?php

namespace App\Http\Controllers\Constants;

class ErrorMessages
{
    // User related errors
    const USER_LOAD_ERROR = 'Lỗi khi tải thông tin người dùng';
    const USER_UPDATE_ERROR = 'Lỗi khi cập nhật thông tin người dùng';
    const USER_UNAUTHORIZED = 'Người dùng chưa đăng nhập';
    const INVALID_ID = 'ID không hợp lệ';
    const INVALID_EMAIL = 'Email không hợp lệ';
    
    // Image related errors
    const IMAGE_LOAD_ERROR = 'Không thể tải dữ liệu ảnh';
    const IMAGE_UPLOAD_ERROR = 'Không thể tải lên';
    const IMAGE_UPDATE_ERROR = 'Không thể cập nhật';
    const IMAGE_DELETE_ERROR = 'Không thể xóa';
    const IMAGE_PROXY_ERROR = 'Lỗi khi proxy hình ảnh';
    const IMAGE_DOWNLOAD_ERROR = 'Lỗi khi tải hình ảnh';
    
    // Comment related errors
    const COMMENT_LOAD_ERROR = 'Lỗi khi lấy bình luận';
    const COMMENT_CREATE_ERROR = 'Lỗi khi tạo bình luận';
    const COMMENT_UPDATE_ERROR = 'Lỗi khi cập nhật bình luận';
    const COMMENT_DELETE_ERROR = 'Lỗi khi xóa bình luận';
    const REPLY_CREATE_ERROR = 'Lỗi khi tạo phản hồi';
    const LIKE_TOGGLE_ERROR = 'Lỗi khi thay đổi trạng thái thích';
    
    // Auth related errors
    const AUTH_CHECK_ERROR = 'Lỗi khi kiểm tra trạng thái xác thực';
    const REGISTER_ERROR = 'Lỗi khi đăng ký';
    const LOGIN_ERROR = 'Lỗi khi đăng nhập';
    const LOGOUT_ERROR = 'Lỗi khi đăng xuất';
    const FORGOT_PASSWORD_ERROR = 'Lỗi khi quên mật khẩu';
    const RESET_PASSWORD_ERROR = 'Lỗi khi đặt lại mật khẩu';
    
    // File related errors
    const INVALID_FILE = 'File không hợp lệ';
    const INVALID_URL = 'URL không hợp lệ';
    const R2_ONLY_SUPPORT = 'Chỉ hỗ trợ URL từ R2.dev';
    
    // Generic messages
    const VALIDATION_ERROR = 'Dữ liệu đầu vào không hợp lệ';
    const PERMISSION_DENIED = 'Không có quyền thực hiện thao tác này';
    const SYSTEM_ERROR = 'Lỗi hệ thống';

    // Feature related errors
    const FEATURE_LOAD_ERROR = 'Lỗi khi tải dữ liệu chức năng';
    const FEATURE_NOT_FOUND = 'Không tìm thấy chức năng';

    // Image Job related errors
    const IMAGE_JOB_LOAD_ERROR = 'Lỗi khi tải dữ liệu tiến trình';
    const IMAGE_JOB_NOT_FOUND = 'Không tìm thấy tiến trình';
    const IMAGE_JOB_CREATE_ERROR = 'Lỗi khi tạo tiến trình';
    const IMAGE_JOB_UPDATE_ERROR = 'Lỗi khi cập nhật tiến trình';
    const IMAGE_JOB_DELETE_ERROR = 'Lỗi khi xóa tiến trình';
    const IMAGE_JOB_RETRY_ERROR = 'Lỗi khi thử lại tiến trình';
    const IMAGE_JOB_CANCEL_ERROR = 'Lỗi khi hủy tiến trình';

    // Google related errors
    const GOOGLE_REDIRECT_ERROR = 'Lỗi khi tạo URL đăng nhập Google';
    const GOOGLE_CALLBACK_ERROR = 'Lỗi khi xử lý callback từ Google';
    const GOOGLE_AUTH_ERROR = 'Lỗi khi đăng nhập Google';
    const GOOGLE_LOGIN_ERROR = 'Lỗi khi đăng nhập Google';

    // Mail related errors
    const MAIL_VERIFY_ERROR = 'Lỗi khi xác thực email';
    const MAIL_RESEND_ERROR = 'Lỗi khi gửi lại mã xác thực email';
    const MAIL_SEND_ERROR = 'Lỗi khi gửi email';
    const MAIL_SEND_PASSWORD_CHANGE_ERROR = 'Lỗi khi gửi email đặt lại mật khẩu';
    const MAIL_SEND_PASSWORD_CHANGE_VERIFICATION_ERROR = 'Lỗi khi gửi email xác thực đặt lại mật khẩu';

    // Forgot Password related errors
    const FORGOT_PASSWORD_SEND_ERROR = 'Lỗi khi gửi email khôi phục mật khẩu';
    const FORGOT_PASSWORD_VERIFY_ERROR = 'Lỗi khi xác thực mã khôi phục mật khẩu';
    const FORGOT_PASSWORD_RESET_ERROR = 'Lỗi khi đặt lại mật khẩu';

    // Notification related errors
    const NOTIFICATION_LOAD_ERROR = 'Lỗi khi tải dữ liệu thông báo';
    const NOTIFICATION_MARK_AS_READ_ERROR = 'Lỗi khi đánh dấu thông báo đã đọc';
    const NOTIFICATION_MARK_ALL_AS_READ_ERROR = 'Lỗi khi đánh dấu tất cả thông báo đã đọc';
    const NOTIFICATION_DELETE_ERROR = 'Lỗi khi xóa thông báo';
} 
