# Hướng dẫn Setup Google2FA

## Tổng quan
Google2FA (Google Two-Factor Authentication) là hệ thống xác thực 2 yếu tố sử dụng mã TOTP (Time-based One-Time Password) để tăng cường bảo mật cho tài khoản người dùng.

## Tính năng
- ✅ Bật/tắt xác thực 2 yếu tố
- ✅ Tạo mã QR để setup với ứng dụng xác thực
- ✅ Xác thực mã 6 số từ ứng dụng
- ✅ Tạo và quản lý mã khôi phục
- ✅ Sử dụng mã khôi phục khi mất thiết bị

## Cài đặt

### 1. Cài đặt dependencies
```bash
composer require pragmarx/google2fa-laravel
composer require simplesoftwareio/simple-qrcode
```

### 2. Chạy migrations
```bash
php artisan migrate
```

### 3. Cấu hình (nếu cần)
Thêm vào file `.env`:
```env
GOOGLE2FA_SECRET_KEY=your-secret-key-here
```

## Sử dụng

### Frontend
Truy cập trang: `/user/google2fa`

### API Endpoints

#### Protected Routes (cần auth)
- `GET /api/v1/auth/2fa/status` - Lấy trạng thái 2FA
- `POST /api/v1/auth/2fa/generate` - Tạo mã QR
- `POST /api/v1/auth/2fa/enable` - Bật 2FA
- `POST /api/v1/auth/2fa/disable` - Tắt 2FA
- `POST /api/v1/auth/2fa/recovery-codes` - Tạo mã khôi phục

#### Public Routes (không cần auth)
- `POST /api/v1/auth/2fa/verify` - Xác thực 2FA
- `POST /api/v1/auth/2fa/recovery` - Sử dụng mã khôi phục

## Luồng hoạt động

### 1. Setup 2FA
1. User click "Bật 2FA"
2. Hệ thống tạo secret key và mã QR
3. User quét mã QR bằng ứng dụng xác thực
4. User nhập mã 6 số để xác nhận
5. Hệ thống kích hoạt 2FA và tạo mã khôi phục

### 2. Đăng nhập với 2FA
1. User đăng nhập với email/password
2. Hệ thống yêu cầu mã 2FA
3. User nhập mã từ ứng dụng xác thực
4. Hệ thống xác thực và cho phép đăng nhập

### 3. Sử dụng mã khôi phục
1. User mất thiết bị xác thực
2. User sử dụng một trong 10 mã khôi phục
3. Hệ thống xác thực và cho phép truy cập
4. Mã khôi phục đã sử dụng sẽ bị vô hiệu hóa

## Bảo mật

### Secret Key
- Được mã hóa trước khi lưu vào database
- Mỗi user có một secret key duy nhất
- Secret key được tạo ngẫu nhiên 32 ký tự

### Mã khôi phục
- Được hash bằng bcrypt trước khi lưu
- Mỗi mã chỉ sử dụng được 1 lần
- Tự động tạo 10 mã mới khi cần

### Rate Limiting
- Giới hạn số lần thử mã xác thực
- Giới hạn số lần tạo mã khôi phục
- Logging các hoạt động đáng ngờ

## Troubleshooting

### Lỗi thường gặp
1. **Mã xác thực không đúng**: Kiểm tra thời gian đồng bộ giữa server và thiết bị
2. **QR code không hiển thị**: Kiểm tra quyền ghi file và cấu hình GD library
3. **Mã khôi phục không hoạt động**: Kiểm tra mã đã được sử dụng chưa

### Debug
Bật debug mode trong `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

## Tài liệu tham khảo
- [Google2FA Laravel Package](https://github.com/PragmaRX/google2fa-laravel)
- [Simple QR Code](https://github.com/SimpleSoftwareIO/simple-qrcode)
- [TOTP Standard](https://tools.ietf.org/html/rfc6238)
