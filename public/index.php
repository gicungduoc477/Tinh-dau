<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Kiểm tra xem ứng dụng có đang ở chế độ bảo trì không...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Đăng ký bộ tải tự động của Composer...
require __DIR__.'/../vendor/autoload.php';

// Khởi tạo Laravel và xử lý yêu cầu...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());