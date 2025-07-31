# Database Seeders

Thư mục này chứa các file seed để tạo dữ liệu mẫu cho database.

## Các file seed có sẵn

### 1. `ai_features_seed.php`
- **Mô tả**: Dữ liệu mẫu cho bảng `ai_features` (các tính năng AI)
- **Số lượng**: 20 bản ghi
- **Nội dung**: Các tính năng tạo ảnh AI như Text to Image, Image to Image, Anime conversion, etc.

### 2. `users_seed.php`
- **Mô tả**: Dữ liệu mẫu cho bảng `users` (người dùng)
- **Số lượng**: 10 bản ghi
- **Nội dung**: Thông tin người dùng với tên tiếng Việt, email, avatar, credits, etc.
- **Password mặc định**: `password123`

### 3. `admin_users_seed.php`
- **Mô tả**: Dữ liệu mẫu cho bảng `admin_users` (quản trị viên)
- **Số lượng**: 5 bản ghi
- **Nội dung**: Các role khác nhau: super_admin, admin, moderator
- **Password mặc định**: `admin123`, `moderator123`, `support123`, `content123`

### 4. `images_seed.php`
- **Mô tả**: Dữ liệu mẫu cho bảng `images` (hình ảnh)
- **Số lượng**: 10 bản ghi
- **Nội dung**: Hình ảnh được tạo từ các tính năng AI khác nhau
- **Trạng thái**: Hầu hết là `public`, 1 bản ghi `private`

### 5. `comments_seed.php`
- **Mô tả**: Dữ liệu mẫu cho bảng `comments` (bình luận)
- **Số lượng**: 20 bản ghi
- **Nội dung**: Bình luận và reply cho các hình ảnh
- **Cấu trúc**: Hỗ trợ comment cha-con (parent_id)

### 6. `interactions_seed.php`
- **Mô tả**: Dữ liệu mẫu cho bảng `interactions` (tương tác)
- **Số lượng**: 41 bản ghi
- **Loại tương tác**: like, save, report
- **Nội dung**: Các tương tác của người dùng với hình ảnh

### 7. `image_jobs_seed.php`
- **Mô tả**: Dữ liệu mẫu cho bảng `image_jobs` (công việc tạo ảnh)
- **Số lượng**: 14 bản ghi
- **Trạng thái**: completed, processing, pending, failed
- **Nội dung**: Các job tạo ảnh với metadata chi tiết

### 8. `reports_seed.php`
- **Mô tả**: Dữ liệu mẫu cho bảng `reports` (báo cáo)
- **Số lượng**: 3 bản ghi
- **Trạng thái**: waiting, accept
- **Nội dung**: Báo cáo từ admin về hình ảnh không phù hợp

## Cách sử dụng

### Chạy tất cả seed
```bash
php artisan db:seed
```

### Chạy seed cụ thể
```bash
php artisan db:seed --class=users_seed
php artisan db:seed --class=images_seed
php artisan db:seed --class=comments_seed
```

### Reset và chạy lại tất cả seed
```bash
php artisan migrate:fresh --seed
```

## Thông tin đăng nhập mẫu

### Người dùng thường
- **Email**: `an.nguyen@example.com`
- **Password**: `password123`

### Admin
- **Email**: `admin@ktai.com`
- **Password**: `admin123`

### Moderator
- **Email**: `moderator1@ktai.com`
- **Password**: `moderator123`

## Lưu ý quan trọng

1. **Thứ tự chạy seed**: Các seed được thiết kế để chạy theo thứ tự trong `DatabaseSeeder.php` để đảm bảo foreign key constraints.

2. **Dữ liệu mẫu**: Tất cả dữ liệu đều là mẫu, không chứa thông tin thật.

3. **URL hình ảnh**: Các URL hình ảnh sử dụng domain mẫu, cần thay đổi khi deploy thực tế.

4. **Timestamps**: Các timestamp được tạo với khoảng cách hợp lý để mô phỏng hoạt động thực tế.

5. **Metadata**: Các bản ghi images và image_jobs chứa metadata JSON với thông tin chi tiết về quá trình tạo ảnh.

## Cấu trúc dữ liệu

### Mối quan hệ chính:
- `users` → `images` (1:N)
- `ai_features` → `images` (1:N)
- `users` → `comments` (1:N)
- `images` → `comments` (1:N)
- `users` → `interactions` (1:N)
- `images` → `interactions` (1:N)
- `users` → `image_jobs` (1:N)
- `ai_features` → `image_jobs` (1:N)
- `admin_users` → `reports` (1:N)
- `images` → `reports` (1:N) 