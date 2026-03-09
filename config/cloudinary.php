<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | File cấu hình cho Laravel wrapper của Cloudinary PHP SDK.
    | Đảm bảo bạn đã thêm CLOUDINARY_URL vào Environment trên Render.
    |
    */

    // Ưu tiên dùng CLOUDINARY_URL vì nó chứa đầy đủ Cloud Name, API Key và Secret
    'cloud_url' => env('CLOUDINARY_URL'),

    /*
     * Bạn cũng có thể cấu hình riêng lẻ (nếu không dùng URL tổng)
     */
    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'),
    'api_secret' => env('CLOUDINARY_API_SECRET'),

    /*
     * Cấu hình cụ thể cho việc Upload
     */
    'uploads' => [
        'use_filename' => true,      // Giữ lại tên file gốc để dễ quản lý trên Cloudinary
        'unique_filename' => true,   // Bật true để tránh việc 2 người cùng up 'hinh-anh.jpg' bị đè nhau
        'overwrite' => false,        // Không ghi đè nếu trùng tên (để an toàn)
        'folder' => 'tinh_dau_shop', // Thư mục mặc định trên Cloudinary
    ],

    /*
     * Cấu hình tối ưu hóa hình ảnh khi lấy về (Fetch)
     */
    'fetch' => [
        'format' => 'auto',          // Tự động chuyển về WebP/Avif nếu trình duyệt hỗ trợ (giúp web nhanh hơn)
        'quality' => 'auto',         // Tự động giảm dung lượng ảnh nhưng vẫn giữ độ nét
    ],
];