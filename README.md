# KT_AI - Ứng dụng tạo ảnh AI

Click vào ảnh để xem video
[![video demo](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20002955.png)](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/demo-ktai.mp4)
![Ảnh auth](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/auth.png)

## Giới thiệu

KT_AI là một ứng dụng web hiện đại cho phép người dùng tạo và quản lý hình ảnh sử dụng trí tuệ nhân tạo thông qua ComfyUI. Dự án này kết hợp sức mạnh của các công nghệ web tiên tiến với hệ thống AI, mang lại trải nghiệm người dùng.

Ứng dụng cung cấp giao diện thân thiện với người dùng, cho phép tùy chỉnh nhiều tham số để tạo ra hình ảnh chất lượng cao theo nhu cầu. Người dùng có thể tạo ra nhiều phong cách nghệ thuật khác nhau như chân thực, phim hoạt hình, phác họa, anime, màu nước, sơn dầu, nghệ thuật số và trừu tượng.

## Công nghệ sử dụng

### Backend
- **Laravel 11**: Framework PHP hiện đại với kiến trúc MVC mạnh mẽ
- **PHP 8.2+**: Tận dụng các tính năng mới nhất của PHP 
- **MySQL**: Hệ quản trị cơ sở dữ liệu
- **Redis**: Cache và quản lý hàng đợi
- **Laravel Sanctum**: Xác thực API
- **Laravel Socialite**: Đăng nhập qua mạng xã hội
- **ComfyUI API**: Tích hợp với API của ComfyUI để tạo hình ảnh AI
- **R2 Storage**: Lưu trữ hình ảnh trên nền tảng đám mây
- **Laravel Websockets/Pusher**: Cung cấp WebSocket server để giao tiếp hai chiều giữa máy chủ và trình duyệt
- **Laravel Events & Broadcasting**: Hệ thống phát sóng sự kiện realtime
- **Laravel Queue & Jobs**: Xử lý tác vụ nặng bất đồng bộ

### Frontend
- **Vue.js 3**: Framework JavaScript sử dụng Composition API
- **Tailwind CSS**: Framework CSS tiện lợi
- **PrimeVue**: Thư viện component UI cho Vue
- **Pinia**: Quản lý state cho Vue 3
- **Vite**: Công cụ build nhanh cho frontend
- **Chart.js**: Hiển thị dữ liệu thống kê
- **Laravel Echo**: Thư viện JavaScript cho kết nối WebSocket và nhận sự kiện từ Laravel
- **Socket.io-client/Pusher JS**: Client WebSocket để nhận thông báo realtime

### CI/CD & Deployment
- **Git**: Quản lý phiên bản
- **GitHub Actions**: Tự động hóa quy trình CI/CD

## Điểm đặc biệt

### Tối ưu hóa hiệu suất ComfyUI
Một trong những điểm mạnh nổi bật của KT_AI là việc tối ưu hóa quy trình làm việc với ComfyUI, cho phép xử lý yêu cầu tạo ảnh **dưới 5 giây** cho mỗi request. Điều này đạt được nhờ:

- Sử dụng các model và thông số hợp lý để tạo ảnh
- Tối ưu hóa templates JSON cho ComfyUI
- Xử lý bất đồng bộ thông qua hàng đợi Laravel
- Cache thông minh giảm thời gian xử lý
- Kết nối trực tiếp với ComfyUI thông qua API riêng biệt

![Ví dụ về workflow](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20015121.png)
![Minh họa tốc độ](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20002603.png)

### Một số tính năng tạo ảnh với Comfyui
Dưới đây là một số tính năng tạo ảnh được sử dụng trong project:

| STT | Tên | Ảnh | Miêu tả |
|-----|-----|-----|---------|
| 1 | Sao chép phong cách | ![IPAdapter](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20002341.png) | Sao chép phong cách ảnh từ người dùng tải lên |
| 2 | Sao chép khuôn mặt | ![HyperLora](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20002359.png) | Sao chép khuôn mặt từ duy nhất một ảnh mà người dùng tải lên |
| 3 | Thử quần áo | ![ACE++](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20002430.png) | Sao chép và thay đổi quần áo |
| 4 | Tạo người mẫu ảo | ![HyperLora + ACE++](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20002410.png) | Sao chép quần áo và khuôn mặt từ 2 ảnh mà người dùng tải lên |
| 5 | Sao chép nhiều phong cách | ![IPAdapter Mix Styles](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20002420.png) | Sao chép và kết hợp nhiều phong cách hình ảnh khác nhau |
| 6 | Chuyển đổi ảnh sang thể loại anime | ![Convert to Anime Style](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/G5.png) | Chuyển đổi ảnh sang thể loại anime |


### Tính năng nổi bật
- **Đa dạng phong cách**: Hỗ trợ nhiều phong cách nghệ thuật khác nhau
- **Tùy chỉnh cao**: Kiểm soát chi tiết thông số như kích thước, prompt, seed
- **Theo dõi tiến trình thời gian thực**: Cập nhật tiến độ tạo ảnh theo thời gian thực qua WebSockets
- **Thông báo realtime**: Nhận thông báo tức thì khi ảnh được tạo thành công hoặc có lỗi
- **Cập nhật tương tác realtime**: Hiển thị ngay lập tức các lượt thích và bình luận mới
- **Trạng thái người dùng realtime**: Hiển thị người dùng đang online/offline
- **Lưu trữ đám mây**: Tự động lưu trữ hình ảnh trên R2 Storage
- **Thông báo**: Hệ thống thông báo khi hoàn thành hoặc xảy ra lỗi
- **Lịch sử rõ ràng**: Quản lý lịch sử tạo ảnh của người dùng
- **Tương tác cộng đồng**: Bình luận và thích hình ảnh trong cộng đồng

## Cài đặt và Cấu hình

### Yêu cầu hệ thống
- PHP 8.2 hoặc mới hơn
- Composer
- Node.js và npm
- MySQL 8.0+
- ComfyUI đã cài đặt (máy chủ độc lập)
- Redis (tùy chọn, nhưng được khuyến nghị)

### Các bước cài đặt

1. **Clone repository:**
   ```bash
   git clone https://github.com/yourusername/kt_ai.git
   cd kt_ai
   ```

2. **Cài đặt dependencies PHP:**
   ```bash
   composer install
   ```

3. **Cài đặt dependencies JavaScript:**
   ```bash
   npm install
   ```

4. **Cấu hình môi trường:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Cấu hình cơ sở dữ liệu:**
   Chỉnh sửa file `.env` với thông tin cơ sở dữ liệu của bạn:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kt_ai
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Cấu hình ComfyUI API:**
   Thêm vào file `.env`:
   ```
   COMFYUI_URL=http://localhost:8188
   ```

7. **Cấu hình R2 Storage:**
   ```
   R2_ACCESS_KEY=your_access_key
   R2_SECRET_KEY=your_secret_key
   R2_ENDPOINT=your_endpoint
   R2_BUCKET=your_bucket
   ```

8. **Chạy migration và seeder:**
   ```bash
   php artisan migrate --seed
   ```

9. **Biên dịch tài nguyên frontend:**
   ```bash
   npm run build
   ```

10. **Khởi động máy chủ development:**
    ```bash
    php artisan serve
    ```

11. **Chạy queue worker để xử lý tạo ảnh:**
    ```bash
    php artisan queue:work
    ```

## Sử dụng

### Tạo ảnh AI

1. Đăng nhập vào hệ thống
2. Truy cập tab "Tạo ảnh" trong menu chính
3. Chọn loại ảnh và phong cách mong muốn
4. Nhập prompt mô tả nội dung ảnh
5. Tùy chỉnh các thông số như kích thước, seed
6. Tải lên ảnh gốc nếu muốn (tùy chọn)
7. Nhấn "Tạo ảnh" và đợi kết quả

### Quản lý ảnh

- Xem tất cả ảnh đã tạo trong tab "Thư viện của tôi"
- Chia sẻ ảnh với cộng đồng
- Tải xuống ảnh với chất lượng cao
- Xem chi tiết mọi thông số đã sử dụng để tạo ảnh

## Screenshots

| STT | Tên | Ảnh | Miêu tả |
|-----|-----|-----|---------|
| 1 | Trang chủ | ![Trang chủ](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20004604.png) | Giao diện quản lý tài khoản, quản lý các hình ảnh tải lên từ tài khoản người dùng |
| 2 | Tạo ảnh | ![Tạo ảnh](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20004000.png) | Giao diện cho phép người dùng nhập prompt, điều chỉnh tham số và tạo ảnh AI |
| 3 | Đăng tải ảnh | ![Đăng tải ảnh](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20004153.png) | Hiển thị tất cả ảnh đã tải lên, tùy chỉnh nội dung sẽ đăng lên |
| 4 | Thông báo Realtime | ![Thông báo Realtime](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20014850.png) | Hiển thị thông báo Realtime đến người dùng |
| 5 | Tương tác bài đăng | ![Tương tác bài đăng](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20014950.png) | Hiển thị hình ảnh đăng và lượt tương tác như comment, reply, like... |
| 6 | Quản lý thông báo | ![Quản lý thông báo](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20004027.png) | Hiển thị các thông báo đã đọc và chưa đọc cũng như chuông thông báo |
| 7 | Quản lý tiến trình | ![Quản lý tiến trình](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20004054.png) | Hiển thị các tiến trình đang thực thi, đã thực thi, thực thi thất bại |
| 8 | Chế độ xác thực email | ![Chế độ xác thực email](https://pub-0ec2d0f968bd484492ed9495327a3698.r2.dev/KT_AI/Screenshot%202025-05-21%20012225.png) | Sử dụng Redis cho cơ chế xác thực email, đổi mật khẩu, quên mật khẩu... |


## Đóng góp

Dự án này được phát triển bởi Khôi Trần. Mọi đóng góp và góp ý đều được chào đón.

## Hướng dẫn sử dụng Queue

Ứng dụng này sử dụng hệ thống Queue để xử lý các tiến trình tạo ảnh bất đồng bộ. Dưới đây là cách thiết lập và sử dụng:

### Thiết lập Database

Đảm bảo bạn đã chạy migration để tạo bảng jobs:

```bash
php artisan migrate
```

### Khởi động Queue Worker

Có 2 cách để khởi động queue worker:

#### 1. Sử dụng lệnh tích hợp

```bash
php artisan queue:start-worker --daemon
```

Các tùy chọn:
- `--conn=database` - Kết nối queue sử dụng (mặc định: database)
- `--queue=default,image-processing,image-processing-low` - Danh sách queue cần xử lý
- `--sleep=3` - Số giây nghỉ khi không có job
- `--tries=3` - Số lần thử lại khi job lỗi
- `--timeout=60` - Thời gian tối đa cho mỗi job (giây)

#### 2. Sử dụng Supervisor (khuyến nghị cho môi trường production)

Tạo file cấu hình Supervisor:

```bash
php artisan queue:make-supervisor-config
```

Các tùy chọn:
- `--file=laravel-worker` - Tên file cấu hình
- `--processes=2` - Số lượng worker
- `--memory=128` - Giới hạn bộ nhớ (MB)

Sau khi tạo file cấu hình, sao chép file này vào thư mục `/etc/supervisor/conf.d/` trên máy chủ production và khởi động Supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

### Theo dõi Queue

Kiểm tra trạng thái Queue:

```bash
php artisan queue:monitor
php artisan queue:size
php artisan queue:failed
```

Dọn dẹp Queue:

```bash
php artisan queue:prune-batches --hours=48
php artisan queue:prune-failed --hours=24
```

### Các Queue được sử dụng

- `default` - Các job thông thường
- `image-processing` - Các job xử lý hình ảnh ưu tiên cao
- `image-processing-low` - Các job xử lý hình ảnh ưu tiên thấp (kiểm tra trạng thái)

## Cấu hình WebSockets và Realtime

Ứng dụng sử dụng WebSockets để cung cấp các tính năng realtime như thông báo và cập nhật tiến trình. Dưới đây là cách thiết lập:

### Thiết lập Laravel WebSockets

1. **Cài đặt Laravel WebSockets**:
   ```bash
   php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="migrations"
   php artisan migrate
   php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider" --tag="config"
   ```

2. **Cấu hình .env**:
   ```
   BROADCAST_DRIVER=pusher
   PUSHER_APP_ID=your_app_id
   PUSHER_APP_KEY=your_app_key
   PUSHER_APP_SECRET=your_app_secret
   PUSHER_HOST=127.0.0.1
   PUSHER_PORT=6001
   PUSHER_SCHEME=http
   PUSHER_APP_CLUSTER=mt1
   ```

3. **Khởi động WebSocket Server**:
   ```bash
   php artisan websockets:serve
   ```

### Sử dụng Laravel Echo (Frontend)

Trong file JavaScript chính:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    wsHost: process.env.MIX_PUSHER_HOST || window.location.hostname,
    wsPort: process.env.MIX_PUSHER_PORT || 6001,
    forceTLS: false,
    disableStats: true,
});

// Lắng nghe sự kiện cập nhật tiến trình
window.Echo.private(`image.processing.${userId}`)
    .listen('ImageProcessingProgress', (e) => {
        console.log(e.progress);
        // Cập nhật UI với thông tin tiến trình
    });

// Lắng nghe thông báo
window.Echo.private(`notifications.${userId}`)
    .listen('NewNotification', (e) => {
        // Hiển thị thông báo mới
    });
```

### Phát sóng sự kiện từ Laravel

Ví dụ về cách phát sóng sự kiện tiến trình:

```php
event(new ImageProcessingProgress($userId, $percentComplete, $imageId));
```

Các loại kênh được sử dụng:
- `private-image.processing.{userId}`: Cập nhật tiến trình tạo ảnh
- `private-notifications.{userId}`: Thông báo cá nhân
- `presence-online`: Trạng thái người dùng online
- `public-interactions.{postId}`: Tương tác trên các bài đăng

### Sau cùng

Dự án đang phát triển, còn nhiều tính năng tạo ảnh trong Comfyui chưa được triển khai 😢.
