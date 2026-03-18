<?php

return [

    // Đổi mặc định thành cloudinary để an toàn cho môi trường Render
    'default' => env('FILESYSTEM_DISK', 'cloudinary'), 

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => public_path('uploads/product'), 
            'url' => env('APP_URL').'/uploads/product',
            'visibility' => 'public',
            'throw' => true,
            'report' => false,
        ],

        // THÊM DISK CLOUDINARY VÀO ĐÂY
        'cloudinary' => [
            'driver' => 'cloudinary',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];