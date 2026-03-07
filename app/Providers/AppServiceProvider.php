<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
// Thêm dòng này để nạp class Config nếu cần, nhưng dùng helper config() cho nhanh
use Illuminate\Support\Facades\Config;

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
        // 1. Cấu hình phân trang Bootstrap 5 (Giữ nguyên của bạn)
        Paginator::useBootstrapFive();

        // 2. ÉP NẠP CẤU HÌNH CLOUDINARY NGAY KHI APP KHỞI ĐỘNG
        // Cách này giúp sửa lỗi "Trying to access array offset on null" 
        // bằng cách đảm bảo cấu hình không bao giờ bị null khi thư viện gọi tới.
        config([
            'cloudinary.cloud_url' => env('CLOUDINARY_URL')
        ]);
    }
}