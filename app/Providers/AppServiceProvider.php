<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Artisan;
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
        // 1. Cấu hình phân trang Bootstrap 5
        Paginator::useBootstrapFive();

        // 2. ÉP DÙNG HTTPS KHI CHẠY TRÊN RENDER
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');

            // 3. TỰ ĐỘNG XÓA CACHE CẤU HÌNH
            try {
                Artisan::call('config:clear');
            } catch (\Exception $e) {
                // Bỏ qua lỗi nếu môi trường không cho phép
            }
        }

        // 4. ÉP NẠP CẤU HÌNH CLOUDINARY
        config([
            'cloudinary.cloud_url' => env('CLOUDINARY_URL')
        ]);

        // 5. ĐĂNG KÝ DRIVER BREVO (Sửa lỗi Unsupported mail transport)
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