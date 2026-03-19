<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

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
        // 1. CẤU HÌNH PHÂN TRANG BOOTSTRAP 5
        // Laravel sẽ render HTML theo cấu trúc của Bootstrap 5
        Paginator::useBootstrapFive();

        // 2. ÉP DÙNG HTTPS VÀ XỬ LÝ PROXY TRÊN RENDER
        // Giúp fix lỗi Mixed Content (mất CSS/JS) khi chạy trên server Render/Heroku
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
            
            if (isset($this->app['request'])) {
                $this->app['request']->server->set('HTTPS', true);
            }
        }

        // 3. CẤU HÌNH CLOUDINARY
        // Nạp cấu hình từ .env vào config hệ thống
        config([
            'cloudinary.cloud_url' => env('CLOUDINARY_URL'),
            'cloudinary.cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
            'cloudinary.upload' => [
                'folder' => env('CLOUDINARY_FOLDER', 'tinh_dau_shop/products'),
            ],
        ]);

        // 4. ĐĂNG KÝ DRIVER BREVO
        // Hỗ trợ gửi mail qua API Brevo
        if (env('BREVO_API_KEY')) {
            Mail::extend('brevo', function (array $config) {
                return (new BrevoTransportFactory)->create(
                    new Dsn(
                        'brevo+api',
                        'default',
                        env('BREVO_API_KEY') 
                    )
                );
            });
        }
    }
}