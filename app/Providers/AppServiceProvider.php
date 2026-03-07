<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Cấu hình phân trang Bootstrap 5
        Paginator::useBootstrapFive();

        // 2. ÉP DÙNG HTTPS KHI CHẠY TRÊN RENDER
        // Giúp sửa lỗi "The information you’re about to submit is not secure" khi đăng nhập
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        // 3. ÉP NẠP CẤU HÌNH CLOUDINARY
        // Giúp sửa lỗi "Trying to access array offset on null" khi upload ảnh
        config([
            'cloudinary.cloud_url' => env('CLOUDINARY_URL')
        ]);
    }
}