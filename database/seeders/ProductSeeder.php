<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo hoặc lấy Category
        $category = Category::firstOrCreate(
            ['slug' => 'tinh-dau-thien-nhien'],
            ['name' => 'Tinh dầu thiên nhiên']
        );

        // 2. Danh sách sản phẩm
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

        // Link ảnh mẫu tinh dầu (Bạn có thể thay bằng link Cloudinary của bạn nếu muốn)
        $sampleImage = 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&q=80&w=500';

        foreach ($products as $p) {
            $slug = $p[1];

            Product::updateOrCreate(
                ['slug' => $slug], 
                [
                    'name' => 'Tinh dầu ' . $p[0],
                    'price' => $p[2],
                    'description' => 'Tinh dầu nguyên chất 100% tự nhiên giúp thư giãn và tốt cho sức khỏe.',
                    'category_id' => $category->id,
                    'stock' => rand(10, 50),
                    'classification' => 'Tinh dầu nguyên chất',
                    // Nếu sản phẩm đã có ảnh rồi thì không ghi đè link mẫu, nếu chưa có thì gán link mẫu
                    'image' => Product::where('slug', $slug)->first()?->image ?? $sampleImage,
                ]
            );
        }
    }
}