<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // Quan trọng: Phải import facade này

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Đăng ký lịch chạy cho Nature Shop
 */
// Tự động chạy lệnh quét đơn hàng định kỳ vào lúc 07:00 sáng mỗi ngày
Schedule::command('subscription:run')->dailyAt('07:00');

// (Tùy chọn) Nếu bạn muốn kiểm tra trong lúc phát triển, có thể cho chạy mỗi phút:
// Schedule::command('subscription:run')->everyMinute();