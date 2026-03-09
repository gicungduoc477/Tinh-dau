<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | File này cấu hình kết nối giữa Laravel và Cloudinary.
    | Đảm bảo bạn đã thêm CLOUDINARY_URL vào Environment trên Render.
    |
    */

    // Đây là biến quan trọng nhất cho Cloudinary trên môi trường Production (Render)
    'cloud_url' => env('CLOUDINARY_URL'),

    'cloud' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ],

    /*
     * Cấu hình chi tiết cho việc Upload
     */
    'upload' => [
        'use_filename' => true,
        'unique_filename' => true,
        'overwrite' => false,
        'folder' => env('CLOUDINARY_FOLDER', 'tinh_dau_shop/products'), 
    ],

    /*
     * Cấu hình mặc định cho việc hiển thị (Transformation)
     */
    'defaults' => [
        'format' => 'auto',
        'quality' => 'auto',
        'secure' => true, // Đảm bảo luôn trả về link https
    ],
];