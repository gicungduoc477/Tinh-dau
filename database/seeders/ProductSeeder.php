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
        $cloudinaryUrl = env('CLOUDINARY_URL');
        $cloudinary = null;
        if ($cloudinaryUrl) {
            $cloudinary = new Cloudinary(Configuration::instance($cloudinaryUrl));
        }

        $category = Category::firstOrCreate(
            ['slug' => 'tinh-dau-thien-nhien'],
            ['name' => 'Tinh dầu thiên nhiên']
        );

        // Thêm cột phân loại vào mảng để seed đa dạng dữ liệu
        // Cấu trúc: [Tên, Slug, Giá, Ảnh, Phân loại]
        $products = [
            ['Sả Chanh', 'tinh-dau-sa-chanh', 150000, 'sa_chanh.jpg', 'Tinh dầu nguyên chất'],
            ['Bạc Hà', 'tinh-dau-bac-ha', 120000, 'bac_ha.jpg', 'Tinh dầu nguyên chất'],
            ['Oải Hương', 'tinh-dau-oai-huong', 250000, 'oai_huong.jpg', 'Tinh dầu nguyên chất'],
            ['Hương Thảo', 'tinh-dau-huong-thao', 230000, 'huong_thao.jpg', 'Tinh dầu nguyên chất'],
            
            // Nhóm Blend Oil (Để test phân loại Hỗn hợp)
            ['Sleep Well', 'tinh-dau-ngu-ngon', 300000, 'sleep_well.jpg', 'Tinh dầu hỗn hợp (Blend Oil)'],
            ['Stress Relief', 'tinh-dau-giam-stress', 320000, 'stress.jpg', 'Tinh dầu hỗn hợp (Blend Oil)'],
            ['Fresh Air', 'tinh-dau-khong-khi-tuoi-moi', 280000, 'fresh.jpg', 'Tinh dầu hỗn hợp (Blend Oil)'],
            
            // Nhóm Fragrance (Để test phân loại Hương liệu)
            ['Hương Nước Hoa Pháp', 'huong-nuoc-hoa-phap', 180000, 'perfume.jpg', 'Hương liệu pha'],
            ['Hương Trà Trắng', 'huong-tra-trang', 160000, 'white_tea.jpg', 'Hương liệu pha'],
            
            ['Tràm Gió', 'tinh-dau-tram-gio', 180000, 'tram_gio.jpg', 'Tinh dầu nguyên chất'],
            ['Vỏ Quế', 'tinh-dau-vo-que', 140000, 'vo_que.jpg', 'Tinh dầu nguyên chất'],
            ['Bưởi Da Xanh', 'tinh-dau-buoi', 190000, 'buoi.jpg', 'Tinh dầu nguyên chất'],
            ['Gỗ Đàn Hương', 'tinh-dau-dan-huong', 450000, 'dan_huong.jpg', 'Tinh dầu nguyên chất'],
            ['Ngọc Lan Tây', 'tinh-dau-ngoc-lan', 220000, 'ngoc_lan.jpg', 'Tinh dầu nguyên chất'],
            ['Hoa Hồng', 'tinh-dau-hoa-hong', 500000, 'hoa_hong.jpg', 'Tinh dầu nguyên chất'],
            ['Chanh Vàng', 'tinh-dau-chanh-vang', 130000, 'chanh_vang.jpg', 'Tinh dầu nguyên chất'],
            ['Cam Ngọt', 'tinh-dau-cam-ngot', 110000, 'cam_ngot.jpg', 'Tinh dầu nguyên chất'],
            ['Khuynh Diệp', 'tinh-dau-khuynh-diep', 160000, 'khuynh_diep.jpg', 'Tinh dầu nguyên chất'],
            ['Gỗ Thông', 'tinh-dau-go-thong', 210000, 'go_thong.jpg', 'Tinh dầu nguyên chất'],
            ['Trà Xanh', 'tinh-dau-tra-xanh', 170000, 'tra_xanh.jpg', 'Tinh dầu nguyên chất'],
        ];

        $sampleImage = 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&q=80&w=500';

        foreach ($products as $p) {
            $slug = $p[1];
            $fileName = $p[3];
            $classification = $p[4]; // Lấy phân loại từ mảng
            $imagePath = public_path('seed_images/' . $fileName);
            $finalUrl = null;

            if ($cloudinary && file_exists($imagePath)) {
                try {
                    $result = $cloudinary->uploadApi()->upload($imagePath, [
                        'folder' => 'tinh_dau_shop/products',
                        'public_id' => $slug
                    ]);
                    $finalUrl = $result['secure_url'];
                } catch (\Exception $e) {
                    Log::error("Seeder Upload Failed for $slug: " . $e->getMessage());
                }
            }

            if (!$finalUrl) {
                $existingProduct = Product::where('slug', $slug)->first();
                $finalUrl = $existingProduct?->image ?? $sampleImage;
            }

            Product::updateOrCreate(
                ['slug' => $slug], 
                [
                    'name' => (str_contains($p[0], 'Hương') ? '' : 'Tinh dầu ') . $p[0],
                    'price' => $p[2],
                    'description' => 'Sản phẩm chất lượng cao giúp không gian sống thêm dễ chịu.',
                    'category_id' => $category->id,
                    'stock' => rand(10, 50),
                    'classification' => $classification, // Cập nhật phân loại đúng ở đây
                    'image' => $finalUrl,
                ]
            );
        }
    }
}