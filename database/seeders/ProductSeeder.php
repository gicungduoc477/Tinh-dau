<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;
use Illuminate\Support\Facades\Log;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cấu hình Cloudinary thủ công (để chạy mượt trên Render)
        $cloudinaryUrl = env('CLOUDINARY_URL');
        $cloudinary = null;
        if ($cloudinaryUrl) {
            $cloudinary = new Cloudinary(Configuration::instance($cloudinaryUrl));
        }

        // 2. Tạo hoặc lấy Category
        $category = Category::firstOrCreate(
            ['slug' => 'tinh-dau-thien-nhien'],
            ['name' => 'Tinh dầu thiên nhiên']
        );

        // 3. Danh sách sản phẩm (Tên, Slug, Giá, Tên file ảnh tương ứng trong public/seed_images)
        $products = [
            ['Sả Chanh', 'tinh-dau-sa-chanh', 150000, 'sa_chanh.jpg'],
            ['Bạc Hà', 'tinh-dau-bac-ha', 120000, 'bac_ha.jpg'],
            ['Oải Hương', 'tinh-dau-oai-huong', 250000, 'oai_huong.jpg'],
            ['Tràm Gió', 'tinh-dau-tram-gio', 180000, 'tram_gio.jpg'],
            ['Vỏ Quế', 'tinh-dau-vo-que', 140000, 'vo_que.jpg'],
            ['Bưởi Da Xanh', 'tinh-dau-buoi', 190000, 'buoi.jpg'],
            ['Gỗ Đàn Hương', 'tinh-dau-dan-huong', 450000, 'dan_huong.jpg'],
            ['Ngọc Lan Tây', 'tinh-dau-ngoc-lan', 220000, 'ngoc_lan.jpg'],
            ['Hoa Hồng', 'tinh-dau-hoa-hong', 500000, 'hoa_hong.jpg'],
            ['Chanh Vàng', 'tinh-dau-chanh-vang', 130000, 'chanh_vang.jpg'],
            ['Cam Ngọt', 'tinh-dau-cam-ngot', 110000, 'cam_ngot.jpg'],
            ['Khuynh Diệp', 'tinh-dau-khuynh-diep', 160000, 'khuynh_diep.jpg'],
            ['Gỗ Thông', 'tinh-dau-go-thong', 210000, 'go_thong.jpg'],
            ['Hương Thảo', 'tinh-dau-huong-thao', 230000, 'huong_thao.jpg'],
            ['Trà Xanh', 'tinh-dau-tra-xanh', 170000, 'tra_xanh.jpg'],
            ['Gừng Tuyết', 'tinh-dau-gung', 195000, 'gung.jpg'],
            ['Hoa Nhài', 'tinh-dau-hoa-nhai', 480000, 'hoa_nhai.jpg'],
            ['Trầm Hương', 'tinh-dau-tram-huong', 900000, 'tram_huong.jpg'],
            ['Kinh Giới', 'tinh-dau-kinh-gioi', 155000, 'kinh_gioi.jpg'],
            ['Sả Hoa Hồng', 'tinh-dau-sa-hoa-hong', 165000, 'sa_hoa_hong.jpg'],
        ];

        // Link ảnh mẫu nếu không tìm thấy file local
        $sampleImage = 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&q=80&w=500';

        foreach ($products as $p) {
            $slug = $p[1];
            $fileName = $p[3];
            $imagePath = public_path('seed_images/' . $fileName);
            $finalUrl = null;

            // Kiểm tra và upload ảnh lên Cloudinary
            if ($cloudinary && file_exists($imagePath)) {
                try {
                    $result = $cloudinary->uploadApi()->upload($imagePath, [
                        'folder' => 'tinh_dau_shop/products',
                        'public_id' => $slug // Giữ slug làm ID ảnh cho đẹp
                    ]);
                    $finalUrl = $result['secure_url'];
                } catch (\Exception $e) {
                    Log::error("Seeder Upload Failed for $slug: " . $e->getMessage());
                }
            }

            // Nếu upload thất bại hoặc không có file, giữ ảnh cũ hoặc dùng ảnh mẫu
            if (!$finalUrl) {
                $existingProduct = Product::where('slug', $slug)->first();
                $finalUrl = $existingProduct?->image ?? $sampleImage;
            }

            Product::updateOrCreate(
                ['slug' => $slug], 
                [
                    'name' => 'Tinh dầu ' . $p[0],
                    'price' => $p[2],
                    'description' => 'Tinh dầu nguyên chất 100% tự nhiên giúp thư giãn và tốt cho sức khỏe.',
                    'category_id' => $category->id,
                    'stock' => rand(10, 50),
                    'classification' => 'Tinh dầu nguyên chất',
                    'image' => $finalUrl,
                ]
            );
        }
    }
}