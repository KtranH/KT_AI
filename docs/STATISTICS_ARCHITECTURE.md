# 📊 Kiến Trúc Statistics API

## 🎯 Tổng Quan

Dự án đã được refactor để sử dụng **Repository Pattern** và **Service Layer** cho Statistics API, giúp tách biệt trách nhiệm và dễ bảo trì.

## 📁 Cấu Trúc Files

```
app/
├── Interfaces/
│   └── StatisticsRepositoryInterface.php    # Interface định nghĩa contract
├── Repositories/
│   └── StatisticsRepository.php            # Implementation của Repository
├── Services/Business/
│   └── StatisticsService.php               # Business logic layer
└── Http/Controllers/V1/System/
    └── StatisticsController.php            # HTTP Controller
```

## 🔧 Các Layer

### 1. **Controller Layer** (`StatisticsController`)
- **Trách nhiệm**: Xử lý HTTP requests/responses
- **Dependency**: `StatisticsService`
- **Methods**:
  - `getStatistics()` - Thống kê hệ thống
  - `getUserStatistics()` - Thống kê người dùng

### 2. **Service Layer** (`StatisticsService`)
- **Trách nhiệm**: Business logic, tính toán phức tạp
- **Dependency**: `StatisticsRepositoryInterface`
- **Methods**:
  - `getSystemStatistics()` - Thống kê hệ thống
  - `getUserStatistics()` - Thống kê người dùng
  - `calculateUserPerformance()` - Tính hiệu suất người dùng

### 3. **Repository Layer** (`StatisticsRepository`)
- **Trách nhiệm**: Truy vấn database, data access
- **Dependency**: Models (User, Image, Comment, Like)
- **Methods**:
  - `getUserOverview()` - Tổng quan người dùng
  - `getMonthlyStats()` - Thống kê theo tháng
  - `getWeeklyStats()` - Thống kê theo tuần
  - `getTopFeatures()` - Top tính năng AI
  - `getHourlyActivity()` - Hoạt động theo giờ

## 🚀 API Endpoints

### **GET** `/api/statistics`
- **Mô tả**: Lấy thống kê tổng quan của hệ thống
- **Auth**: Không cần
- **Response**: 
```json
{
  "totalUsers": 1500,
  "activeUsers": 1200,
  "newUsersToday": 50,
  "totalImages": 5000,
  "imagesUploadedToday": 100,
  "totalComments": 12000,
  "commentsToday": 300,
  "likesToday": 1500
}
```

### **GET** `/api/user-statistics`
- **Mô tả**: Lấy thống kê chi tiết cho người dùng đăng nhập
- **Auth**: Sanctum required
- **Response**:
```json
{
  "overview": {
    "totalImages": 25,
    "totalLikes": 150,
    "totalComments": 45,
    "remainingCredits": 75,
    "memberSince": "15/01/2025",
    "today": {
      "images": 3,
      "likes": 12,
      "comments": 5
    }
  },
  "monthlyStats": {
    "labels": ["Tháng 1", "Tháng 2", ...],
    "data": [
      {"images": 5, "likes": 20, "comments": 8},
      ...
    ]
  },
  "weeklyStats": {
    "labels": ["Tuần 1", "Tuần 2", ...],
    "data": [
      {"images": 3, "likes": 15, "comments": 5},
      ...
    ]
  },
  "topFeatures": [
    {"id": 1, "name": "Text to Image", "count": 15},
    ...
  ],
  "hourlyActivity": [
    {"hour": 0, "images": 2, "likes": 5, "comments": 1},
    ...
  ]
}
```

## 🔄 Dependency Injection

### Service Provider Registration
```php
// app/Providers/AppServiceProvider.php
$this->app->bind(StatisticsRepositoryInterface::class, StatisticsRepository::class);
```

### Constructor Injection
```php
// Controller
public function __construct(StatisticsService $statisticsService)

// Service
public function __construct(StatisticsRepositoryInterface $statisticsRepository)
```

## 📈 Lợi Ích Của Kiến Trúc Này

### ✅ **Tách Biệt Trách Nhiệm**
- **Controller**: Chỉ xử lý HTTP
- **Service**: Business logic
- **Repository**: Data access

### ✅ **Dễ Test**
- Có thể mock từng layer riêng biệt
- Unit test cho từng component

### ✅ **Tái Sử Dụng**
- Repository có thể dùng cho nhiều Service
- Service có thể dùng cho nhiều Controller

### ✅ **Dễ Mở Rộng**
- Thêm method mới trong Repository
- Thêm logic mới trong Service
- Không ảnh hưởng layer khác

## 🧪 Testing

### Unit Test Repository
```php
// tests/Unit/Repositories/StatisticsRepositoryTest.php
public function test_get_user_overview()
{
    $user = User::factory()->create();
    $repository = new StatisticsRepository();
    
    $result = $repository->getUserOverview($user);
    
    $this->assertArrayHasKey('totalImages', $result);
    $this->assertArrayHasKey('totalLikes', $result);
}
```

### Unit Test Service
```php
// tests/Unit/Services/StatisticsServiceTest.php
public function test_calculate_user_performance()
{
    $user = User::factory()->create();
    $repository = Mockery::mock(StatisticsRepositoryInterface::class);
    $service = new StatisticsService($repository);
    
    $repository->shouldReceive('getUserOverview')
        ->with($user)
        ->andReturn([
            'totalImages' => 10,
            'totalLikes' => 50,
            'totalComments' => 20,
            'remainingCredits' => 80
        ]);
    
    $result = $service->calculateUserPerformance($user);
    
    $this->assertEquals(50, $result['performanceScore']);
}
```

## 🔧 Cách Sử Dụng

### 1. **Thêm Method Mới**
```php
// 1. Thêm vào Interface
public function getCustomStats(User $user): array;

// 2. Implement trong Repository
public function getCustomStats(User $user): array
{
    // Logic truy vấn database
}

// 3. Thêm vào Service
public function getCustomStatistics(User $user): array
{
    return $this->statisticsRepository->getCustomStats($user);
}

// 4. Thêm vào Controller
public function getCustomStatistics(Request $request): JsonResponse
{
    $user = Auth::user();
    $data = $this->statisticsService->getCustomStatistics($user);
    return $this->successResponseV1($data);
}
```

### 2. **Cache Implementation**
```php
// Trong Repository
public function getUserOverview(User $user): array
{
    return Cache::remember("user_stats_{$user->id}", 3600, function () use ($user) {
        // Logic truy vấn database
    });
}
```

## 📝 Best Practices

1. **Repository**: Chỉ chứa logic truy vấn database
2. **Service**: Chứa business logic, tính toán phức tạp
3. **Controller**: Chỉ xử lý HTTP request/response
4. **Interface**: Định nghĩa contract rõ ràng
5. **Dependency Injection**: Sử dụng constructor injection
6. **Error Handling**: Xử lý lỗi ở mỗi layer
7. **Caching**: Implement cache ở Repository layer
8. **Testing**: Viết test cho từng layer

## 🎯 Kết Luận

Kiến trúc này giúp code **dễ bảo trì**, **dễ test**, và **dễ mở rộng**. Mỗi layer có trách nhiệm rõ ràng và có thể phát triển độc lập. 