# Hướng Dẫn Sử Dụng Redis Trong Dự Án KT_AI

## Giới Thiệu

Redis (Remote Dictionary Server) là một kho lưu trữ dữ liệu dạng key-value trong bộ nhớ, mã nguồn mở, cực kỳ nhanh, được sử dụng rộng rãi như một cơ sở dữ liệu, bộ nhớ đệm và message broker. Trong dự án KT_AI, Redis đóng vai trò quan trọng trong nhiều thành phần của ứng dụng: từ lưu trữ token xác thực, bộ nhớ cache, quản lý hàng đợi (queue) cho đến các tác vụ real-time.

Tài liệu này sẽ giải thích chi tiết về cách Redis được triển khai và sử dụng trong dự án KT_AI, giúp các thành viên dự án hiểu rõ về cấu trúc và cách tận dụng hiệu quả Redis trong phát triển.

## Cấu Hình Redis

### Cấu Hình Trong File .env

```env
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6380
REDIS_DB=0
REDIS_CACHE_DB=1
```

### Cấu Hình Cơ Bản Trong database.php

```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'predis'),
    
    'options' => [
        'cluster' => env('REDIS_CLUSTER', 'redis'),
        'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
    ],
    
    'default' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', '6380'),
        'database' => env('REDIS_DB', '0'),
    ],
    
    'cache' => [
        'url' => env('REDIS_URL'),
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'username' => env('REDIS_USERNAME'),
        'password' => env('REDIS_PASSWORD'),
        'port' => env('REDIS_PORT', '6380'),
        'database' => env('REDIS_CACHE_DB', '1'),
    ],
],
```

### Cấu Hình Cache (cache.php)

```php
'redis' => [
    'driver' => 'redis',
    'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
    'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
],
```

### Cấu Hình Queue (queue.php)

```php
'redis' => [
    'driver' => 'redis',
    'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
    'queue' => env('REDIS_QUEUE', 'default'),
    'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
    'block_for' => null,
    'after_commit' => false,
],
```

### Cấu Hình Session (session.php)

```php
'driver' => env('SESSION_DRIVER', 'database'),
// Redis có thể được cấu hình như driver bằng cách đổi giá trị SESSION_DRIVER='redis' trong file .env
```

## Sử Dụng Redis Trong Dự Án

### 1. Lưu Trữ Và Xác Thực Mã Xác Minh

Redis được sử dụng để lưu trữ các mã xác thực gửi qua email với thời hạn nhất định:

```php
// Trong MailService.php
public function sendMail($user)
{
    try
    {
        $verificationcode = Str::random(6);
        Redis::setex("verify_code:{$user->email}", 600, $verificationcode);
        
        // Gửi email chứa mã xác thực
        $detail = [
            'title' => "Mã xác nhận từ KT-AI",
            'begin' => "Xin chào " . $user->name,
            'body' => "Chúng tôi nhận được lượt đăng ký tài khoản của bạn. Nhập mã dưới đây để hoàn tất xác minh (Lưu ý mã chỉ có khả dụng trong 10 phút)",
            'code' => $verificationcode
        ];
        Mail::to($user->email)->send(new VerificationMail($detail));
        return true;
    }
    catch (Exception $e)
    {
        Log::error('Lỗi khi gửi email xác nhận: ' . $e->getMessage());
        return false;
    }
}
```

### 2. Giới Hạn Yêu Cầu (Rate Limiting)

Redis được sử dụng để theo dõi số lượng yêu cầu gửi lại mã xác thực:

```php
// Trong ForgotPasswordService.php
// Lưu biến đếm số lần gửi mã
$emailKey = "password_reset_code_send_count:{$request->email}";
$attempts = Redis::incr($emailKey);
if ($attempts === 1) {
    Redis::expire($emailKey, 300); // 5 phút
}

// Kiểm tra biến đếm có vượt quá số lần chưa
if ($attempts > 3) {
    $ttl = Redis::ttl($emailKey);

    throw ValidationException::withMessages([
        'email' => ["Bạn đã yêu cầu quá số lần cho phép. Vui lòng thử lại sau {$ttl} giây."]
    ]);
}
```

### 3. Lưu Trữ Token Đặt Lại Mật Khẩu

```php
// Lưu token với thời hạn 10 phút
Redis::setex("password_reset_token:{$request->email}", 600, $token);
```

### 4. Kiểm Tra Kết Nối Redis

Dự án có route kiểm tra để đảm bảo kết nối Redis hoạt động đúng:

```php
Route::get('/test/redis', function() {
    try {
        $ping = Redis::connection()->ping();
        return response()->json([
            'status' => 'success',
            'message' => 'Kết nối thành công',
            'ping' => $ping,
            'set_value' => Redis::set('test', 'test'),
            'get_value' => Redis::get('test'),
        ]);
    } catch(\Exception $e) {
        report($e); // Ghi log lỗi vào storage/logs/laravel.log
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});
```

## Redis Cache

### Cấu Hình Cache Với Redis

Trong dự án KT_AI, Redis được cấu hình như một driver cho hệ thống bộ nhớ đệm (cache):

```php
// config/cache.php
'default' => env('CACHE_STORE', 'database'), // Có thể đặt thành 'redis'

'stores' => [
    // Các driver cache khác...
    
    'redis' => [
        'driver' => 'redis',
        'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
        'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
    ],
],
```

### Sử Dụng Redis Cache

```php
// Lưu dữ liệu vào cache
Cache::store('redis')->put('key', 'value', $seconds);

// Lấy dữ liệu từ cache
$value = Cache::store('redis')->get('key');

// Kiểm tra tồn tại
if (Cache::store('redis')->has('key')) {
    // xử lý...
}

// Xóa khỏi cache
Cache::store('redis')->forget('key');

// Cache với thời gian TTL
Cache::store('redis')->put('key', 'value', now()->addMinutes(10));

// Cache với callback cho giá trị không tồn tại
$value = Cache::store('redis')->remember('key', $seconds, function () {
    return DB::table('expensive_query')->get();
});
```

## Redis Queue

### Cấu Hình Redis Queue Driver

Dự án sử dụng database là driver mặc định cho queue, nhưng đã cấu hình sẵn Redis để có thể chuyển đổi dễ dàng:

```php
// config/queue.php
'default' => env('QUEUE_CONNECTION', 'database'), // Có thể chuyển thành 'redis'

'connections' => [
    // Các driver queue khác...
    
    'redis' => [
        'driver' => 'redis',
        'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
        'block_for' => null,
        'after_commit' => false,
    ],
],
```

### Chuyển Đổi Sang Redis Queue

Để chuyển từ database queue sang Redis queue:

1. Thay đổi giá trị trong file .env:
```env
QUEUE_CONNECTION=redis
```

2. Chạy queue worker với kết nối Redis:
```bash
php artisan queue:work redis --queue=default,image-processing,image-processing-low
```

### Lợi Ích Của Redis Queue So Với Database Queue

1. **Hiệu suất cao hơn**: Redis lưu trữ trong bộ nhớ nên việc đọc/ghi nhanh hơn database
2. **Ít tải cho database**: Giảm tải cho database, đặc biệt khi có nhiều job
3. **Xử lý realtime tốt hơn**: Độ trễ thấp hơn, phù hợp với các ứng dụng realtime
4. **Hỗ trợ các cấu trúc dữ liệu phức tạp**: Redis có các cấu trúc dữ liệu như lists, sets, sorted sets...

## Redis Cho Session

### Cấu Hình Session Với Redis

```php
// config/session.php
'driver' => env('SESSION_DRIVER', 'database'), // Có thể chuyển thành 'redis'

// Khi sử dụng Redis cho session
'connection' => env('SESSION_CONNECTION', 'default'),
'store' => env('SESSION_STORE', null),
```

### Chuyển Sang Redis Session

Để sử dụng Redis cho quản lý session:

1. Cập nhật .env:
```env
SESSION_DRIVER=redis
SESSION_CONNECTION=default
```

2. Đảm bảo đã cài đặt predis hoặc phpredis extension:
```bash
composer require predis/predis
```

## Redis Cho Broadcasting

### Cấu Hình Redis Cho Broadcasting

```php
// config/broadcasting.php
'connections' => [
    // Các drivers khác...
    
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
    ],
],
```

## Sử Dụng Facade Redis Trực Tiếp

### Các Lệnh Redis Cơ Bản

```php
// Kết nối tới Redis
$redis = Redis::connection();

// Lưu trữ giá trị
Redis::set('key', 'value');

// Lưu trữ với thời gian hết hạn (giây)
Redis::setex('key', 60, 'value'); // Hết hạn sau 60 giây

// Lấy giá trị
$value = Redis::get('key');

// Kiểm tra tồn tại
$exists = Redis::exists('key');

// Xóa key
Redis::del('key');

// Tăng giá trị
$newValue = Redis::incr('counter');

// Kiểm tra thời gian còn lại của key (Time To Live)
$ttl = Redis::ttl('key');

// Đặt thời gian hết hạn cho key đã tồn tại
Redis::expire('key', 60); // 60 giây
```

### Các Lệnh Redis Phức Tạp Hơn

```php
// Danh sách (List)
Redis::lpush('list', 'value1'); // Thêm vào đầu
Redis::rpush('list', 'value2'); // Thêm vào cuối
Redis::lpop('list'); // Lấy và xóa từ đầu
Redis::rpop('list'); // Lấy và xóa từ cuối
$all = Redis::lrange('list', 0, -1); // Lấy tất cả

// Hash
Redis::hset('user:1', 'name', 'John');
Redis::hset('user:1', 'email', 'john@example.com');
$name = Redis::hget('user:1', 'name');
$all = Redis::hgetall('user:1');

// Set
Redis::sadd('tags', 'tag1', 'tag2');
Redis::sismember('tags', 'tag1'); // Kiểm tra thành viên
$all = Redis::smembers('tags'); // Lấy tất cả

// Sorted Set
Redis::zadd('leaderboard', 100, 'user1');
Redis::zadd('leaderboard', 200, 'user2');
$top = Redis::zrevrange('leaderboard', 0, 2); // Top 3
```

## Redis Transaction

### Thực Hiện Giao Dịch Redis

```php
Redis::transaction(function ($redis) {
    $redis->set('key1', 'value1');
    $redis->set('key2', 'value2');
});
```

### Theo Dõi Và Transaction

```php
Redis::watch('key');

$value = Redis::get('key');

// Nếu key thay đổi, transaction sẽ thất bại
$result = Redis::transaction(function ($redis) use ($value) {
    $redis->set('key', $value + 1);
});
```

## Kiểm Tra Và Giám Sát Redis

### Kiểm Tra Kết Nối Redis

```php
try {
    $ping = Redis::connection()->ping();
    echo "Redis kết nối thành công: " . $ping;
} catch (\Exception $e) {
    echo "Redis không thể kết nối: " . $e->getMessage();
}
```

### Redis Info Command

```php
$info = Redis::command('info');
```

### Theo Dõi Key Trong Redis

```php
// Lấy tất cả các keys theo mẫu
$keys = Redis::keys('user:*');

// Xóa tất cả các keys theo mẫu
foreach (Redis::keys('temp:*') as $key) {
    Redis::del($key);
}
```

## Các Tình Huống Sử Dụng Redis Trong Dự Án

### 1. Xác Minh Email Và Đăng Ký

```php
// Lưu mã xác minh
Redis::setex("verify_code:{$email}", 600, $verificationCode);

// Kiểm tra khi người dùng nhập mã
$storedCode = Redis::get("verify_code:{$email}");
if ($storedCode === $userInputCode) {
    // Xác minh thành công
}
```

### 2. Đặt Lại Mật Khẩu

```php
// Lưu token đặt lại mật khẩu
Redis::setex("password_reset_token:{$email}", 600, $token);

// Xác minh token
$storedToken = Redis::get("password_reset_token:{$email}");
if ($storedToken && $storedToken === $requestToken) {
    // Token hợp lệ
}
```

### 3. Giới Hạn Request (Rate Limiting)

```php
$key = "rate_limit:{$ipAddress}:{$endpoint}";
$attempts = Redis::incr($key);
if ($attempts === 1) {
    Redis::expire($key, 60); // Reset sau 1 phút
}

if ($attempts > $maxAttempts) {
    return response('Quá nhiều yêu cầu, vui lòng thử lại sau.', 429);
}
```

### 4. Cache Dữ Liệu Phổ Biến

```php
$cacheKey = "popular_images_page_{$page}";
$images = Cache::store('redis')->remember($cacheKey, 60*10, function () use ($page) {
    return DB::table('images')
        ->orderBy('views', 'desc')
        ->paginate(10, ['*'], 'page', $page);
});
```

## Thực Hành Tốt Nhất

### 1. Đặt Thời Gian Hết Hạn (TTL) Cho Keys

Luôn đặt thời gian hết hạn cho keys để tránh Redis hết bộ nhớ:

```php
Redis::setex('key', 3600, 'value'); // Hết hạn sau 1 giờ
```

### 2. Sử Dụng Tiền Tố Cho Keys

Sử dụng tiền tố để tổ chức và tránh xung đột:

```php
Redis::set('user:profile:1', json_encode($userData));
Redis::set('user:preferences:1', json_encode($preferences));
```

### 3. Xử Lý Dữ Liệu Lớn

Khi lưu trữ dữ liệu lớn trong Redis, cân nhắc sử dụng nén:

```php
$compressedData = gzcompress(json_encode($largeData));
Redis::set('large:data:key', $compressedData);

$retrieved = Redis::get('large:data:key');
$original = json_decode(gzuncompress($retrieved));
```

### 4. Sao Lưu Dữ Liệu Redis

Redis cung cấp cơ chế lưu trữ dữ liệu vào đĩa (persistence):

```bash
# Trong file cấu hình redis.conf
appendonly yes
appendfsync everysec
```

### 5. Giám Sát Redis

Sử dụng công cụ như Redis Commander, phpRedisAdmin hoặc RedisInsight để giám sát và quản lý Redis.

## Troubleshooting

### Lỗi Kết Nối

```php
try {
    Redis::ping();
} catch (\Exception $e) {
    Log::error('Lỗi kết nối Redis: ' . $e->getMessage());
    
    // Kiểm tra địa chỉ và port Redis
    Log::info('Redis Host: ' . config('database.redis.default.host'));
    Log::info('Redis Port: ' . config('database.redis.default.port'));
}
```

### Xóa Tất Cả Keys (Flushall)

Cẩn thận khi sử dụng lệnh flushall vì nó sẽ xóa tất cả dữ liệu:

```php
// Chỉ sử dụng trong môi trường phát triển!
if (app()->environment('local')) {
    Redis::flushall();
}
```

### Out Of Memory

Nếu Redis báo lỗi "Out of memory", điều chỉnh maxmemory và chính sách xóa (eviction policy):

```
maxmemory 256mb
maxmemory-policy allkeys-lru
```

## Kết Luận

Redis đóng vai trò quan trọng trong dự án KT_AI, cung cấp nhiều tính năng mạnh mẽ cho việc lưu trữ cache, quản lý queue, session và các dữ liệu tạm thời như mã xác thực. Việc sử dụng Redis đúng cách sẽ giúp tăng hiệu suất, giảm tải cho cơ sở dữ liệu chính và cải thiện trải nghiệm người dùng.

Trong tương lai, dự án có thể xem xét mở rộng việc sử dụng Redis bằng cách:

1. Chuyển đổi hoàn toàn từ database queue sang Redis queue để cải thiện hiệu suất
2. Triển khai Redis Cluster hoặc Redis Sentinel cho môi trường sản xuất với độ sẵn sàng cao
3. Tối ưu hóa các chiến lược cache với Redis
4. Triển khai các tính năng thời gian thực sử dụng Redis Pub/Sub