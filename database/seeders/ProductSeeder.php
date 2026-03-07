<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 public function run(): void
{
    $category = \App\Models\Category::firstOrCreate(
        ['name' => 'Tinh dầu thiên nhiên'], 
        ['slug' => 'tinh-dau-thien-nhien']
    );

    $products = [
        ['Sả Chanh', 'tinh-dau-sa-chanh', 150000],
        ['Bạc Hà', 'tinh-dau-bac-ha', 120000],
        ['Oải Hương', 'tinh-dau-oai-huong', 250000],
        ['Tràm Gió', 'tinh-dau-tram-gio', 180000],
        ['Vỏ Quế', 'tinh-dau-vo-que', 140000],
        ['Bưởi Da Xanh', 'tinh-dau-buoi', 190000],
        ['Gỗ Đàn Hương', 'tinh-dau-dan-huong', 450000],
        ['Ngọc Lan Tây', 'tinh-dau-ngoc-lan', 220000],
        ['Hoa Hồng', 'tinh-dau-hoa-hong', 500000],
        ['Chanh Vàng', 'tinh-dau-chanh-vang', 130000],
        ['Cam Ngọt', 'tinh-dau-cam-ngot', 110000],
        ['Khuynh Diệp', 'tinh-dau-khuynh-diep', 160000],
        ['Gỗ Thông', 'tinh-dau-go-thong', 210000],
        ['Hương Thảo', 'tinh-dau-huong-thao', 230000],
        ['Trà Xanh', 'tinh-dau-tra-xanh', 170000],
        ['Gừng Tuyết', 'tinh-dau-gung', 195000],
        ['Hoa Nhài', 'tinh-dau-hoa-nhai', 480000],
        ['Trầm Hương', 'tinh-dau-tram-huong', 900000],
        ['Kinh Giới', 'tinh-dau-kinh-gioi', 155000],
        ['Sả Hoa Hồng', 'tinh-dau-sa-hoa-hong', 165000],
    ];

    foreach ($products as $p) {
        // ensure unique slug if there's a collision
        $baseSlug = $p[1];
        $slug = $baseSlug;
        $i = 1;
        while (\App\Models\Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        \App\Models\Product::updateOrCreate(
            ['slug' => $slug], // Nếu trùng slug thì cập nhật, chưa có thì tạo mới
            [
                'name' => 'Tinh dầu ' . $p[0],
                'price' => $p[2],
                'description' => 'Tinh dầu nguyên chất 100% tự nhiên giúp thư giãn và tốt cho sức khỏe.',
                'category_id' => $category->id,
                'stock' => rand(10, 50),
                'slug' => $slug,
            ]
        );
    }
}
}
