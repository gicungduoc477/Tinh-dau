<?php

/**
 * Cloudinary Laravel SDK Configuration
 * * File cấu hình kết nối giữa Laravel và Cloudinary.
 * Lưu ý: Các biến môi trường phải được cài đặt trên Render Dashboard.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary URL
    |--------------------------------------------------------------------------
    |
    | Đây là cách nhanh nhất để cấu hình trên Render bằng cách sử dụng 
    | CLOUDINARY_URL lấy từ Dashboard của Cloudinary.
    |
    */

    'cloud_url' => env('CLOUDINARY_URL'),

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Cloud Details
    |--------------------------------------------------------------------------
    |
    | Nếu không dùng CLOUDINARY_URL, hệ thống sẽ sử dụng 3 thông số dưới đây.
    |
    */

    'cloud' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    |
    | Cấu hình mặc định khi thực hiện upload file lên Cloudinary.
    |
    */

    'upload' => [
        'use_filename' => true,
        'unique_filename' => true,
        'overwrite' => false,
        'folder' => env('CLOUDINARY_FOLDER', 'tinh_dau_shop/products'), 
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Settings
    |--------------------------------------------------------------------------
    |
    | Tối ưu hóa việc hiển thị ảnh (nén ảnh, tự động chọn định dạng tốt nhất).
    |
    */

    'defaults' => [
        'format' => 'auto',
        'quality' => 'auto',
        'secure' => true, // Luôn ưu tiên HTTPS để tránh lỗi Mixed Content trên Render
    ],

];