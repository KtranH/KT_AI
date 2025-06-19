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

// Kiểm tra và xóa các jobs đã quá hạn
Schedule::command('queue:prune-batches --hours=48')
    ->daily()
    ->description('Dọn dẹp các queue jobs đã quá hạn');

// Kiểm tra và xóa các jobs thất bại đã quá 24 giờ
Schedule::command('queue:prune-failed --hours=24')
    ->daily()
    ->description('Dọn dẹp các queue jobs thất bại quá 24 giờ'); 