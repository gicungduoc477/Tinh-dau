<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. Tin tưởng Proxy của Render để nhận diện đúng giao thức HTTPS
        $middleware->trustProxies(at: '*');

        // 2. Đảm bảo các link trong ứng dụng luôn dùng HTTPS khi chạy thực tế
        // (Bạn cũng có thể cấu hình thêm ở AppServiceProvider nếu muốn triệt để hơn)
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Cấu hình xử lý lỗi tại đây
    })->create();