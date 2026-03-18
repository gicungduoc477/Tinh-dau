<?php

return [
    // Hỗ trợ link tổng hợp từ Render
    'cloud_url' => env('CLOUDINARY_URL'),

    // Cấu trúc mảng 'cloud' để fix lỗi "Undefined array key 'cloud'"
    'cloud' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ],

    'upload' => [
        'use_filename' => true,
        'unique_filename' => true,
        'folder' => env('CLOUDINARY_FOLDER', 'tinh_dau_shop/products'), 
    ],

    'defaults' => [
        'format' => 'auto',
        'quality' => 'auto',
        'secure' => true,
    ],
];