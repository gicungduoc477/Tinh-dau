<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    */

    // Ép dùng smtp làm mặc định nếu trên Render chưa nhận biến MAIL_MAILER
    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            // Điền thẳng smtp.gmail.com làm phương án dự phòng (fallback)
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            // Điền thẳng cổng 465 để tránh bị dùng nhầm cổng 2525
            'port' => env('MAIL_PORT', 465), 
            'username' => env('MAIL_USERNAME', 'vanhieubui403@gmail.com'),
            'password' => env('MAIL_PASSWORD', 'cyssexhzcbatfpyl'),
            'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => ['transport' => 'ses'],
        'postmark' => ['transport' => 'postmark'],
        'resend' => ['transport' => 'resend'],
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],
        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],
        'array' => ['transport' => 'array'],
        'failover' => [
            'transport' => 'failover',
            'mailers' => ['smtp', 'log'],
            'retry_after' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'vanhieubui403@gmail.com'),
        'name' => env('MAIL_FROM_NAME', 'Nature Shop'),
    ],

];