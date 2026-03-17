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

        // 2. ÉP DÙNG HTTPS VÀ XỬ LÝ PROXY TRÊN RENDER
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
            
            // Đảm bảo Laravel hiểu các link được tạo ra là HTTPS
            $this->app['request']->server->set('HTTPS', true);
        }

        // 3. CẤU HÌNH CLOUDINARY
        // Ưu tiên nạp từ env trực tiếp để tránh lỗi cache config
        if (env('CLOUDINARY_URL')) {
            config([
                'cloudinary.cloud_url' => env('CLOUDINARY_URL')
            ]);
        }

        // 4. ĐĂNG KÝ DRIVER BREVO (Sửa lỗi Unsupported mail transport)
        if (env('BREVO_API_KEY')) {
            Mail::extend('brevo', function (array $config) {
                return (new BrevoTransportFactory)->create(
                    new Dsn(
                        'brevo+api',
                        'default',
                        null, // User không cần thiết cho Brevo API
                        env('BREVO_API_KEY')
                    )
                );
            });
        }
    }
}