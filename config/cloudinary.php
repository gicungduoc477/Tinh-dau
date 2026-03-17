<?php

return [
    // Đây là dòng quan trọng nhất - Render sẽ dùng link này để kết nối
    'cloud_url' => env('CLOUDINARY_URL'),

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