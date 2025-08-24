# Tích hợp 2FA vào quá trình đăng nhập

## Tổng quan
Đã tích hợp kiểm tra 2FA vào quá trình đăng nhập cho cả đăng nhập thông thường và đăng nhập qua Google OAuth.

## Các thay đổi đã thực hiện

### 1. AuthService.php
- **Method `login()`**: Thêm kiểm tra 2FA sau khi xác thực thành công
- **Logic mới**: 
  - Nếu user có 2FA được bật → trả về response `2FA_Required` với thông tin user
  - Nếu không có 2FA → tiếp tục đăng nhập bình thường
- **Response format**: Trả về thông tin cần thiết để frontend hiển thị form 2FA

### 2. GoogleService.php
- **Method `handleCallback()`**: Thêm kiểm tra 2FA cho Google OAuth
- **Logic mới**: 
  - Nếu user có 2FA → trả về response yêu cầu xác thực 2FA
  - Nếu không có 2FA → tiếp tục đăng nhập Google bình thường
- **Dependency**: Thêm `Google2FAService` vào constructor

### 3. Google2FAController.php
- **Method `verifyLogin2FA()`**: Gọi service để xác thực 2FA và hoàn tất đăng nhập thông thường
- **Method `verifyGoogleOAuth2FA()`**: Gọi service để xác thực 2FA và hoàn tất đăng nhập Google OAuth
- **Logic**: Controller chỉ xử lý request/response, logic nghiệp vụ được chuyển vào service

### 4. Google2FAService.php
- **Method `verifyLogin2FA()`**: Xử lý logic xác thực 2FA và tạo session cho đăng nhập thông thường
- **Method `verifyGoogleOAuth2FA()`**: Xử lý logic xác thực 2FA và tạo session cho Google OAuth
- **Logic chung**: 
  - Xác thực mã 2FA
  - Tìm user và tạo session
  - Regenerate session để tăng bảo mật
  - Trả về thông tin user đã đăng nhập

### 4. Routes (auth.php)
- **Route mới**: `/2fa/verify-login` - cho đăng nhập thông thường
- **Route mới**: `/2fa/verify-google-oauth` - cho Google OAuth
- **Access**: Public routes (không cần auth)
- **Controller**: Gọi service để xử lý logic nghiệp vụ

### 5. API Service (google2fa.js)
- **Method mới**: `verifyLogin2FA()` - gọi API xác thực 2FA đăng nhập
- **Method mới**: `verifyGoogleOAuth2FA()` - gọi API xác thực 2FA Google OAuth

### 6. Composable (useGoogle2FA.js)
- **Method mới**: `verifyGoogleOAuth2FA()` - xử lý xác thực 2FA Google OAuth
- **Cập nhật**: `verifyLogin2FA()` để sử dụng API mới
- **Logic**: Cập nhật auth store và redirect sau khi xác thực thành công

## Luồng hoạt động mới

### Đăng nhập thông thường
1. User nhập email/password
2. Hệ thống xác thực thông tin đăng nhập
3. **Nếu có 2FA**: Trả về response yêu cầu xác thực 2FA
4. **Nếu không có 2FA**: Đăng nhập thành công ngay lập tức

### Đăng nhập Google OAuth
1. User xác thực qua Google
2. Hệ thống tìm/tạo user từ Google data
3. **Nếu có 2FA**: Trả về response yêu cầu xác thực 2FA
4. **Nếu không có 2FA**: Đăng nhập Google thành công ngay lập tức

### Xác thực 2FA
1. User nhập mã 2FA
2. Hệ thống xác thực mã
3. **Nếu đúng**: Tạo session và hoàn tất đăng nhập
4. **Nếu sai**: Trả về lỗi

## Lợi ích
- **Bảo mật cao**: 2FA được áp dụng cho tất cả phương thức đăng nhập
- **UX tốt**: User được thông báo rõ ràng về việc cần xác thực 2FA
- **Linh hoạt**: Hỗ trợ cả đăng nhập thông thường và OAuth
- **Nhất quán**: Sử dụng cùng logic xác thực 2FA cho mọi trường hợp

## Lưu ý
- Cần test kỹ các luồng đăng nhập khác nhau
- Đảm bảo session được tạo đúng cách sau khi xác thực 2FA
- Kiểm tra việc xử lý lỗi và rollback session khi cần thiết
