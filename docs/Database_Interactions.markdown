# PHÂN TÍCH CƠ SỞ DỮ LIỆU VÀ TƯƠNG TÁC TRONG DỰ ÁN KT_AI

## 1. Giới thiệu

Dự án KT_AI sử dụng MySQL làm hệ quản trị cơ sở dữ liệu chính và tận dụng nhiều cơ chế tương tác khác nhau để tối ưu hóa hiệu suất. Tài liệu này phân tích cấu trúc cơ sở dữ liệu và các phương pháp giao tiếp với database được sử dụng trong dự án.

## 2. Cấu trúc cơ sở dữ liệu

### 2.1. Bảng người dùng và xác thực

- **users**: Lưu trữ thông tin người dùng với các trường như id, name, email, password, avatar_url, sum_like, sum_img, remaining_credits
- **user_sessions**: Theo dõi phiên đăng nhập người dùng
- **admin_users**: Quản lý tài khoản quản trị viên
- **admin_permissions**: Quyền hạn của quản trị viên
- **admin_role_permissions**: Mối quan hệ giữa vai trò và quyền hạn

### 2.2. Bảng chức năng AI và xử lý hình ảnh

- **ai_features**: Lưu trữ các tính năng AI có sẵn (id, title, slug, description, prompt_template, credit_cost...)
- **images**: Lưu trữ hình ảnh đã tạo (id, user_id, features_id, prompt, image_url, privacy_status...)
- **image_jobs**: Quản lý các công việc xử lý hình ảnh (id, user_id, feature_id, prompt, status, comfy_prompt_id...)

### 2.3. Bảng tương tác và hoạt động người dùng

- **interactions**: Lưu trữ tương tác của người dùng với hình ảnh (like, save, report)
- **comments**: Lưu trữ bình luận của người dùng
- **reports**: Quản lý báo cáo nội dung không phù hợp
- **activities_users**: Theo dõi hoạt động của người dùng
- **notifications**: Quản lý thông báo cho người dùng

### 2.4. Bảng hệ thống

- **jobs**: Quản lý hàng đợi công việc
- **failed_jobs**: Lưu trữ công việc bị lỗi
- **cache**: Lưu trữ dữ liệu cache
- **personal_access_tokens**: Quản lý token truy cập API

## 3. Phương pháp tương tác với cơ sở dữ liệu

### 3.1. Eloquent ORM

KT_AI sử dụng Eloquent ORM của Laravel làm phương pháp chính để tương tác với cơ sở dữ liệu:

```php
// Ví dụ tạo bản ghi mới
$image = new Image();
$image->user_id = $userId;
$image->features_id = $featureId;
$image->prompt = $prompt;
$image->image_url = $imageUrl;
$image->save();

// Ví dụ truy vấn với quan hệ
$user = User::with(['images', 'interactions'])->find($userId);
```

### 3.2. Database Queue

Dự án sử dụng hàng đợi (queue) dựa trên cơ sở dữ liệu để xử lý các tác vụ nặng, đặc biệt là công việc tạo hình ảnh AI:

- Sử dụng bảng `jobs` để lưu trữ các công việc cần xử lý
- Phân chia thành nhiều queue khác nhau: `default`, `image-processing`, `image-processing-low`
- Đặt ưu tiên xử lý cho các loại công việc khác nhau

```php
// Ví dụ thêm công việc vào queue
CheckImageJobStatus::dispatch($imageJob->id, $promptId)
    ->onQueue('image-processing')
    ->delay(now()->addSeconds(15));
```

### 3.3. Redis Cache và Session

Redis được sử dụng song song với MySQL để tối ưu hiệu suất:

- **Cache dữ liệu**: Lưu trữ dữ liệu truy xuất thường xuyên
  ```php
  Cache::store('redis')->put('user:'.$userId, $userData, 3600);
  ```

- **Lưu trữ session**: Quản lý phiên người dùng
  ```php
  // Cấu hình trong session.php
  'driver' => env('SESSION_DRIVER', 'redis'),
  ```

- **Xác thực và rate limiting**: Lưu trữ mã xác thực, giới hạn yêu cầu
  ```php
  Redis::setex("verify_code:{$user->email}", 600, $verificationcode);
  ```

### 3.4. Tương tác trực tiếp với ComfyUI API

Dự án giao tiếp với ComfyUI API để xử lý tác vụ tạo hình ảnh AI:

- Gửi prompt và thông số tới ComfyUI API
- Kiểm tra trạng thái xử lý thông qua API
- Lưu trữ kết quả vào database sau khi hoàn thành

```php
// Ví dụ gửi yêu cầu đến ComfyUI API
$response = Http::post('http://127.0.0.1:8188/prompt', [
    'prompt' => $workflow,
    'client_id' => $clientId
]);
```

## 4. Migration và quản lý schema

KT_AI sử dụng migration của Laravel để quản lý schema cơ sở dữ liệu:

- Các file migration định nghĩa cấu trúc bảng và mối quan hệ
- Sử dụng các phương thức như `Schema::create()`, `Schema::table()` để tạo và cập nhật cấu trúc
- Hỗ trợ rollback để khôi phục trạng thái trước đó

```php
// Ví dụ migration tạo bảng
Schema::create('images', function (Blueprint $table) {
    $table->increments('id');
    $table->unsignedInteger('user_id');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    $table->unsignedInteger('features_id');
    $table->foreign('features_id')->references('id')->on('ai_features')->onDelete('cascade');
    $table->text('prompt');
    $table->string('image_url');
    // ...
});
```

## 5. Đảm bảo tính nhất quán dữ liệu

Dự án sử dụng nhiều cơ chế để đảm bảo tính nhất quán dữ liệu:

- **Foreign key constraints**: Ràng buộc khóa ngoại để đảm bảo tính toàn vẹn dữ liệu
  ```php
  $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
  ```

- **Transaction**: Đảm bảo tính toàn vẹn khi thực hiện nhiều thao tác liên quan
  ```php
  DB::transaction(function () use ($userId, $imageId) {
      // Các thao tác database
  });
  ```

- **Queue sau khi commit**: Đảm bảo dữ liệu được lưu trước khi xử lý tiếp
  ```php
  dispatch(new ProcessImage($imageId))->afterCommit();
  ```

## 6. Kết luận

KT_AI sử dụng nhiều phương pháp tương tác với cơ sở dữ liệu, kết hợp giữa MySQL và Redis để tối ưu hiệu suất. Eloquent ORM là công cụ chính để tương tác với database, trong khi hệ thống queue được sử dụng để xử lý các tác vụ nặng. Việc sử dụng Redis làm cache và quản lý session giúp cải thiện đáng kể thời gian phản hồi cho người dùng.

Cấu trúc cơ sở dữ liệu được thiết kế hợp lý với các mối quan hệ rõ ràng giữa người dùng, hình ảnh, tính năng AI và các tương tác. Migration được sử dụng để quản lý schema một cách hiệu quả, cho phép dễ dàng cập nhật và duy trì cấu trúc cơ sở dữ liệu theo thời gian. 