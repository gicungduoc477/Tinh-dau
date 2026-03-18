<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. Kiểm tra chế độ bảo trì
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// 2. Đăng ký Composer Autoloader
require __DIR__.'/../vendor/autoload.php';

// 3. Khởi tạo Laravel và xử lý Request
// Đảm bảo đường dẫn tới bootstrap/app.php chính xác
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());