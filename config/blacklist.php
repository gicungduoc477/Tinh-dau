<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Danh sách từ ngữ nhạy cảm / cấm
    |--------------------------------------------------------------------------
    | Hệ thống sẽ kiểm tra và chặn các nội dung chứa các từ khóa này.
    */
    'words' => [
        // Từ ngữ thô tục
        'dm', 'vcl', 'clm', 'đm', 'vờ lờ', 'đệt',
        
        // Từ ngữ lừa đảo / cạnh tranh
        'lừa đảo', 'lua dao', 'đồ giả', 'hang gia', 'fake',
        
        // Thông tin nhạy cảm khác
        'admin ngu', 'web tệ', 'shopee', 'lazada', 'tiki'
    ],
];