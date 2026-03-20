<?php

// Bắt đầu với cấu hình mặc định
$config = [
    'cloud_url' => env('CLOUDINARY_URL'),
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
        'secure' => true, // Ép link ảnh trả về là https
    ],
];

// Nếu CLOUDINARY_URL tồn tại, phân tích và ghi đè cài đặt 'cloud'
// Đây là cách làm an toàn cho môi trường cache config (render.com)
$cloudinaryUrl = env('CLOUDINARY_URL');
if ($cloudinaryUrl) {
    $parsedUrl = parse_url($cloudinaryUrl);
    if ($parsedUrl && isset($parsedUrl['host'], $parsedUrl['user'], $parsedUrl['pass'])) {
        $config['cloud']['cloud_name'] = $parsedUrl['host'];
        $config['cloud']['api_key']    = $parsedUrl['user'];
        $config['cloud']['api_secret'] = $parsedUrl['pass'];
    }
}

return $config;