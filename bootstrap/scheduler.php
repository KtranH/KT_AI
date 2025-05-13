<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Schedule Tasks
|--------------------------------------------------------------------------
|
| Đây là nơi bạn có thể định nghĩa tất cả các tác vụ định kỳ cho ứng dụng.
| Các tác vụ này sẽ được Laravel chạy theo lịch trình đã định.
|
*/

Schedule::command('image-jobs:check')
    ->everyMinute()
    ->description('Kiểm tra và cập nhật trạng thái các tiến trình tạo ảnh'); 