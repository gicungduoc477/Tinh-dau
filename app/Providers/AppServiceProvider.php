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
            // Ép tất cả URL được tạo ra (asset, route) phải dùng https
            URL::forceScheme('https');
            
            // Fix lỗi mất giao diện CSS/JS do Mixed Content trên Render
            if (isset($this->app['request'])) {
                $this->app['request']->server->set('HTTPS', true);
            }
        }

        // 3. CẤU HÌNH CLOUDINARY (Fix triệt để Undefined array key "cloud")
        // Nạp cấu hình từ ENV vào Config để ghi đè mọi cache cũ trên server
        if (env('CLOUDINARY_URL') || env('CLOUDINARY_CLOUD_NAME')) {
            config([
                'cloudinary.cloud_url' => env('CLOUDINARY_URL'),
                'cloudinary.cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
                // Thêm defaults để thư viện không báo lỗi thiếu index
                'cloudinary.upload' => [
                    'folder' => env('CLOUDINARY_FOLDER', 'tinh_dau_shop/products'),
                ],
            ]);
        }

        // 4. ĐĂNG KÝ DRIVER BREVO (Fix lỗi Unsupported mail transport)
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