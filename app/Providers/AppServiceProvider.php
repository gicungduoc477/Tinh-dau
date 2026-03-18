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
        // 1. Cấu hình phân trang Bootstrap 5
        Paginator::useBootstrapFive();

        // 2. ÉP DÙNG HTTPS VÀ XỬ LÝ PROXY TRÊN RENDER
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
            
            // Đảm bảo Laravel hiểu các link được tạo ra là HTTPS
            if (isset($this->app['request'])) {
                $this->app['request']->server->set('HTTPS', true);
            }
        }

        // 3. CẤU HÌNH CLOUDINARY (Bổ sung để fix lỗi Undefined array key "cloud")
        // Việc nạp cứng vào config tại đây sẽ ghi đè lên các file config bị lỗi cache
        if (env('CLOUDINARY_URL') || env('CLOUDINARY_CLOUD_NAME')) {
            config([
                'cloudinary.cloud_url' => env('CLOUDINARY_URL'),
                'cloudinary.cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
            ]);
        }

        // 4. ĐĂNG KÝ DRIVER BREVO
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