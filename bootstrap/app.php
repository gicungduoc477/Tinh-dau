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
        // Cấu hình ngoại lệ CSRF cho Webhook của PayOS
        // Điều này cho phép server PayOS gửi dữ liệu về hệ thống của bạn mà không bị chặn
        $middleware->validateCsrfTokens(except: [
            '/payment/webhook', 
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();