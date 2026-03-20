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
        Paginator::useBootstrapFive();

        // 2. ÉP DÙNG HTTPS VÀ XỬ LÝ PROXY TRÊN RENDER
        // Giúp fix lỗi "Not Secure" và mất CSS/JS khi chạy trên server Render
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
            
            // Ép buộc request nhận diện là HTTPS để các hàm asset() sinh link bảo mật
            if (isset($this->app['request'])) {
                $this->app['request']->server->set('HTTPS', 'on');
            }
        }

        // 3. CẤU HÌNH CLOUDINARY (TỐI ƯU)
        // Nếu dùng Cloudinary SDK, nó sẽ tự động nhận diện biến CLOUDINARY_URL từ .env
        // Việc để trống ở đây giúp tránh lỗi ghi đè mảng config gây ra lỗi "Undefined array key"
        if (env('CLOUDINARY_URL')) {
            config(['cloudinary.cloud_url' => env('CLOUDINARY_URL')]);
        }

        // 4. ĐĂNG KÝ DRIVER BREVO (MAILER)
        // Cho phép gửi mail qua API Brevo thay vì SMTP truyền thống
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