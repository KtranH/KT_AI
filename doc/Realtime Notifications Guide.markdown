# Hướng Dẫn Sử Dụng Thông Báo Realtime Trong Dự Án KT_AI

## Giới Thiệu

Thông báo realtime là một thành phần quan trọng trong các ứng dụng web hiện đại, cho phép người dùng nhận được thông tin cập nhật ngay lập tức mà không cần phải làm mới trang. Trong dự án KT_AI, hệ thống thông báo realtime được triển khai kết hợp giữa Laravel và Vue.js để thông báo cho người dùng về các sự kiện như tương tác với ảnh, bình luận và tiến trình xử lý ảnh bằng AI.

Tài liệu này sẽ giải thích chi tiết về cấu trúc, cách hoạt động và cách sử dụng hệ thống thông báo realtime trong dự án KT_AI.

## Kiến Trúc Hệ Thống Thông Báo

### Các Thành Phần Chính

1. **Laravel Broadcasting**: Hệ thống phát sóng sự kiện realtime của Laravel
2. **Laravel Notifications**: Framework thông báo tích hợp trong Laravel
3. **Laravel WebSockets**: Server WebSocket cho Laravel (thay thế cho dịch vụ Pusher)
4. **Laravel Echo**: Thư viện JavaScript để lắng nghe sự kiện từ server Laravel
5. **Pusher-JS**: Thư viện JavaScript cho kết nối WebSocket

### Luồng Hoạt Động

1. **Phát sự kiện**: Backend Laravel tạo và phát sóng thông báo
2. **Truyền tải**: WebSocket Server nhận và truyền thông báo đến các kênh đã đăng ký
3. **Nhận thông báo**: Frontend lắng nghe sự kiện thông qua Laravel Echo và xử lý thông báo

## Cấu Hình Hệ Thống

### Cấu Hình WebSocket Server

File `.env`:

```plaintext
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1
```

### Cấu Hình Broadcasting (config/broadcasting.php)

```php
'default' => env('BROADCAST_DRIVER', 'null'),

'connections' => [
    'pusher' => [
        'driver' => 'pusher',
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'app_id' => env('PUSHER_APP_ID'),
        'options' => [
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'host' => env('PUSHER_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
            'port' => env('PUSHER_PORT', 443),
            'scheme' => env('PUSHER_SCHEME', 'https'),
            'encrypted' => true,
            'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
        ],
    ],
    // Cấu hình khác...
],
```

### Kênh Thông Báo (routes/channels.php)

```php
// Kênh private cho thông báo của người dùng
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
```

### Kích Hoạt Broadcasting (app/Providers/BroadcastServiceProvider.php)

```php
public function boot(): void
{
    // Broadcast routes đã được đăng ký trong routes/web.php
    require base_path('routes/channels.php');
}
```

## Định Nghĩa Thông Báo

### Cấu Trúc Cơ Bản Của Một Thông Báo

Các notification trong dự án đều kế thừa từ class `Illuminate\Notifications\Notification` và triển khai interface `ShouldBroadcast` để hỗ trợ broadcasting:

```php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;

class MyNotification extends Notification implements ShouldBroadcast
{
    use Queueable;
    
    // Các kênh gửi thông báo
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }
    
    // Dữ liệu lưu trong database
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'notification_type',
            'message' => 'Nội dung thông báo',
            // Các thông tin khác...
        ];
    }
    
    // Dữ liệu được broadcast qua WebSocket
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'notification_id' => $this->id,
            'type' => 'notification_type',
            'message' => 'Nội dung thông báo',
            // Các thông tin khác...
        ]);
    }
    
    // Tùy chỉnh tên sự kiện
    public function broadcastType(): string
    {
        return 'custom.event';
    }
}
```

### Các Loại Thông Báo Trong Dự Án

1. **ImageGeneratedNotification**: Thông báo khi ảnh được tạo thành công
2. **ImageGenerationFailedNotification**: Thông báo khi quá trình tạo ảnh thất bại
3. **LikeImageNotification**: Thông báo khi có người thích ảnh
4. **AddCommentNotification**: Thông báo khi có người bình luận ảnh
5. **LikeCommentNotification**: Thông báo khi có người thích bình luận
6. **AddReplyNotification**: Thông báo khi có người trả lời bình luận

## Gửi Thông Báo

### Gửi Thông Báo Từ Controller Và Service

Gửi thông báo trong dự án thường được thực hiện thông qua phương thức `notify()` của model User:

```php
// Ví dụ khi tạo ảnh thành công
$user->notify(new ImageGeneratedNotification($imageJob));

// Ví dụ khi có người thích ảnh
$image->user->notify(new LikeImageNotification($interaction, $liker, $image));
```

### Gửi Thông Báo Trong Job

Trong Job `CheckImageJobStatus`, thông báo được gửi khi tiến trình hoàn thành hoặc thất bại:

```php
// Gửi thông báo lỗi cho người dùng
$user = $imageJob->user;
if ($user) {
    $user->notify(new \App\Notifications\ImageGenerationFailedNotification($imageJob));
    Log::debug("Đã gửi thông báo thất bại cho người dùng {$user->id}");
}
```

Trong service `ComfyUIJobService`, thông báo được gửi khi quá trình tạo ảnh hoàn thành:

```php
// Gửi thông báo cho người dùng
$user = $job->user;
if ($user) {
    $user->notify(new ImageGeneratedNotification($job));
    Log::debug("Đã gửi thông báo hoàn thành cho người dùng {$user->id}");
}
```

## Nhận Và Hiển Thị Thông Báo (Frontend)

### Cấu Hình Echo Trong Frontend

File `resources/js/bootstrap.js`:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    },
    // Xử lý lỗi kết nối
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                // Lấy CSRF token mới nhất mỗi khi xác thực
                options.auth.headers['X-CSRF-TOKEN'] = getCsrfToken();
                
                axios.post('/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                }, {
                    headers: options.auth.headers
                })
                .then(response => {
                    callback(false, response.data);
                })
                .catch(error => {
                    console.error('Laravel Echo xác thực kênh thất bại:', error);
                    callback(true, error);
                });
            }
        };
    }
});
```

### Composable useNotifications

File `resources/js/composables/user/useNotifications.js` cung cấp một composable để quản lý thông báo:

```javascript
import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth/authStore'
import { toast } from 'vue-sonner'
import { notificationAPI } from '@/services/api'

export function useNotifications(loadMore = false) {
    const notifications = ref([])
    const unreadCount = ref(0)
    const loading = ref(false)
    const currentPage = ref(1)
    const hasMorePages = ref(true)
    const authStore = useAuthStore()
    let echo = null
    
    // Lấy danh sách thông báo
    const fetchNotifications = async (page = 1, filter = 'all', append = false) => {
        // Logic lấy thông báo...
    }
    
    // Thiết lập Echo để lắng nghe sự kiện thông báo
    const setupEchoListeners = () => {
        if (!window.Echo || !authStore.isAuthenticated || !authStore.user) {
            return
        }
        
        const userId = authStore.user.id
        
        try {
            echo = window.Echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {
                    // Thêm thông báo mới vào đầu danh sách
                    notifications.value.unshift(notification)
                    unreadCount.value += 1
                    
                    // Hiển thị thông báo trên giao diện
                    showNotification(notification)
                })
                
            console.log('Đã thiết lập kênh thông báo cho user:', userId)
        } catch (error) {
            console.error('Lỗi khi thiết lập lắng nghe thông báo:', error)
        }
    }
    
    // Hiển thị thông báo (sử dụng Notification API hoặc thay thế)
    const showNotification = (notification) => {
        // Logic hiển thị thông báo...
    }
    
    // Lifecycle hooks
    onMounted(() => {
        if (authStore.isAuthenticated) {
            fetchNotifications()
            setupEchoListeners()
        }
        requestNotificationPermission()
    })

    onUnmounted(() => {
        // Dọn dẹp listeners khi component unmount
        if (echo) {
            echo.stopListening('notification')
            echo = null
        }
    })
    
    // Return các giá trị và phương thức
    return {
        notifications,
        unreadCount,
        loading,
        hasMorePages,
        fetchNotifications,
        loadMoreNotifications,
        markAsRead,
        markAllAsRead,
        fetchUnreadCount
    }
}
```

### Sử Dụng Composable useNotifications Trong Component Vue

```javascript
<script setup>
import { useNotifications } from '@/composables/user/useNotifications'

// Sử dụng composable
const { 
    notifications, 
    unreadCount, 
    loading, 
    markAsRead,
    markAllAsRead 
} = useNotifications()

// Xử lý khi người dùng click vào thông báo
const handleNotificationClick = (notification) => {
    markAsRead(notification.id)
    // Xử lý dựa trên loại thông báo
    if (notification.type === 'like_image') {
        // Chuyển hướng đến trang ảnh
    } else if (notification.type === 'add_comment') {
        // Chuyển hướng đến bình luận
    }
}
</script>

<template>
  <div class="notifications-panel">
    <div class="header">
      <h3>Thông báo ({{ unreadCount }})</h3>
      <button @click="markAllAsRead">Đánh dấu tất cả đã đọc</button>
    </div>
    
    <div v-if="loading" class="loading">
      Đang tải...
    </div>
    
    <template v-else>
      <div v-if="notifications.length === 0" class="empty-state">
        Không có thông báo nào
      </div>
      
      <div v-else class="notifications-list">
        <div 
          v-for="notification in notifications" 
          :key="notification.id"
          class="notification-item"
          :class="{ 'unread': !notification.read_at }"
          @click="handleNotificationClick(notification)"
        >
          <!-- Hiển thị thông báo tùy theo loại -->
          <div class="content" v-if="notification.type === 'like_image'">
            <img :src="notification.liker_avatar" class="avatar" />
            <span>{{ notification.message }}</span>
          </div>
          
          <!-- Các loại thông báo khác... -->
        </div>
      </div>
    </template>
  </div>
</template>
```

## Quản Lý Thông Báo (Backend)

### Repository (app/Repositories/NotificationRepository.php)

```php
namespace App\Repositories;

use App\Interfaces\NotificationRepositoryInterface;
use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * Lấy thông báo của người dùng
     */
    public function getNotification($user)
    {
        if (!$user) {
            return DatabaseNotification::query()->whereRaw('1=0');
        }

        return DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id);
    }

    /**
     * Đếm số lượng thông báo chưa đọc
     */
    public function countUnreadNotifications($user): int
    {
        if (!$user) {
            return 0;
        }

        return DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Đánh dấu tất cả thông báo là đã đọc
     */
    public function updateReadAllNotifications($user): void
    {
        if (!$user) {
            return;
        }

        DatabaseNotification::where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
```

### Controller (app/Http/Controllers/API/NotificationController.php)

```php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;
    
    public function __construct(NotificationService $notificationService) 
    {
        $this->notificationService = $notificationService;
    }
    
    public function index(Request $request): ResourceCollection
    {
        return $this->notificationService->index($request);
    }
    
    public function markAsRead(string $id): JsonResponse
    {
        return $this->notificationService->markAsRead($id);
    }
    
    public function markAllAsRead(): JsonResponse
    {
        return $this->notificationService->markAllAsRead();
    }
}
```

## Các Tình Huống Sử Dụng Thông Báo

### 1. Thông Báo Khi Ảnh Được Tạo Thành Công

Khi một tiến trình tạo ảnh hoàn thành, `ComfyUIJobService` sẽ gửi thông báo:

```php
// Trong ComfyUIJobService.php
$user->notify(new ImageGeneratedNotification($job));
```

Frontend sẽ nhận thông báo và hiển thị:
- Thông báo popup trên giao diện
- Cập nhật đếm số thông báo chưa đọc
- Hiển thị trong panel thông báo

### 2. Thông Báo Khi Có Người Tương Tác Với Ảnh

Khi có người thích ảnh, bình luận hoặc trả lời bình luận, hệ thống sẽ gửi thông báo cho chủ sở hữu:

```php
// Trong LikeController.php hoặc CommentController.php
$image->user->notify(new LikeImageNotification($interaction, $user, $image));
```

### 3. Thông Báo Khi Có Lỗi Xảy Ra

Khi tiến trình tạo ảnh gặp lỗi, hệ thống sẽ thông báo cho người dùng:

```php
// Trong CheckImageJobStatus.php
$user->notify(new ImageGenerationFailedNotification($imageJob));
```

## Khởi Động Và Vận Hành Hệ Thống

### Khởi Động WebSocket Server

```bash
php artisan websockets:serve
```

### Giám Sát WebSocket Connections

Laravel WebSockets cung cấp dashboard để giám sát kết nối và debug, truy cập tại:

```
http://your-app-url/laravel-websockets
```

### Cấu Hình Supervisor Để Đảm Bảo WebSocket Server Luôn Hoạt Động

```ini
[program:websockets]
command=php /path/to/your/project/artisan websockets:serve
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/websockets.log
```

## Thực Hành Tốt Nhất

1. **Đưa thông báo vào queue**: Sử dụng queue để xử lý thông báo để tránh làm chậm request chính

```php
class ImageGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    // ...
}
```

2. **Giữ dữ liệu thông báo nhỏ gọn**: Chỉ đưa những thông tin cần thiết vào thông báo để tránh tốn băng thông

3. **Xử lý lỗi kết nối**: Frontend nên có cơ chế xử lý và kết nối lại khi mất kết nối với WebSocket Server

4. **Quản lý thông báo trùng lặp**: Kiểm tra và tránh gửi thông báo trùng lặp đến người dùng

5. **Bảo mật**: Luôn đảm bảo xác thực người dùng trước khi cho phép lắng nghe trên các kênh private

## Kết Luận

Hệ thống thông báo realtime trong KT_AI cung cấp trải nghiệm tương tác tức thì cho người dùng, giúp họ luôn cập nhật với các hoạt động mới như tương tác và tiến trình xử lý hình ảnh. Việc kết hợp Laravel Notifications, Broadcasting và WebSockets tạo ra một giải pháp mạnh mẽ, dễ mở rộng cho các tính năng thông báo trong thời gian thực.

Các cải tiến có thể xem xét trong tương lai:
1. Thêm hỗ trợ thông báo đẩy (Push Notifications) cho thiết bị di động
2. Tùy chỉnh cài đặt thông báo cho người dùng
3. Phân nhóm và lọc thông báo theo loại
4. Tối ưu hiệu suất cho số lượng kết nối lớn