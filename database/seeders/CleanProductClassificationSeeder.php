<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class CleanProductClassificationSeeder extends Seeder
{
    public function run()
    {
        // Cập nhật tất cả sản phẩm đang có nhãn "FRAGRANCE" thành "Tinh dầu nguyên chất"
        // để khớp với link ?class=Tinh dầu nguyên chất
        Product::where('classification', 'like', '%FRAGRANCE%')
                ->orWhereNull('classification')
                ->update(['classification' => 'Tinh dầu nguyên chất']);

        $this->command->info('Đã đồng bộ tên phân loại thành công!');
    }
}