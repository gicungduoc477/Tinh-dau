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
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');

            // 3. TỰ ĐỘNG XÓA CACHE CẤU HÌNH (Dành cho Render bản Free)
            // Giúp Laravel nhận đúng cấu hình Mail Port 465 và Driver SMTP
            try {
                Artisan::call('config:clear');
                Artisan::call('cache:clear');
            } catch (\Exception $e) {
                // Bỏ qua nếu có lỗi trong quá trình xóa cache
            }
        }

        // 4. ÉP NẠP CẤU HÌNH CLOUDINARY
        config([
            'cloudinary.cloud_url' => env('CLOUDINARY_URL')
        ]);
    }
}