<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | Đây là cấu hình chuẩn cho thư viện cloudinary-laravel.
    | Nó sẽ lấy dữ liệu từ các biến Environment bạn đã thêm trên Render.
    |
    */

    'cloud_url' => env('CLOUDINARY_URL'),

    /**
     * Upload Preset (Tùy chọn)
     * Nếu bạn có tạo Upload Preset trên Cloudinary thì điền vào đây.
     */
    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),

    /**
     * Mảng 'cloud' - ĐÂY CHÍNH LÀ NƠI GÂY RA LỖI NẾU THIẾU
     */
    'cloud' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ],

];