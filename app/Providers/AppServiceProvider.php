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
        // Giúp fix lỗi cảnh báo "Not Secure" khi nhấn nút Trả hàng/Khiếu nại
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
            
            if (isset($this->app['request'])) {
                $this->app['request']->server->set('HTTPS', 'on');
            }
        }

        // 3. CẤU HÌNH CLOUDINARY (FIX LỖI UNDEFINED ARRAY KEY CLOUD)
        // Lưu ý: Chúng ta ghi đè trực tiếp key cloud_url. 
        // Thư viện sẽ ưu tiên dùng cái này và bỏ qua file config/cloudinary.php bị lỗi.
        if (env('CLOUDINARY_URL')) {
            config([
                'cloudinary.cloud_url' => env('CLOUDINARY_URL'),
            ]);
        }

        // 4. ĐĂNG KÝ DRIVER BREVO (MAILER)
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