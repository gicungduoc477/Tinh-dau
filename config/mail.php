<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | Laravel sẽ ưu tiên lấy giá trị MAIL_MAILER từ file .env.
    | Trên Render, hãy đảm bảo đặt MAIL_MAILER=sendinblue
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],

        // Cấu hình Brevo API (Đã cập nhật transport thành 'brevo')
        'sendinblue' => [
            'transport' => 'brevo', 
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
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