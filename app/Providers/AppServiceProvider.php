<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Artisan;

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
        // Sửa lỗi submit không bảo mật khi đăng nhập/đăng ký
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');

            // 3. TỰ ĐỘNG XÓA CACHE CẤU HÌNH (Dành cho Render bản Free)
            // Giúp Laravel nhận đúng cấu hình Port 465 từ file config/mail.php
            try {
                Artisan::call('config:clear');
            } catch (\Exception $e) {
                // Bỏ qua nếu môi trường Render bản Free không cho phép chạy lệnh nội bộ
            }
        }

        // 4. ÉP NẠP CẤU HÌNH CLOUDINARY
        config([
            'cloudinary.cloud_url' => env('CLOUDINARY_URL')
        ]);
    }
}