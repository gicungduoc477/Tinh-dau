<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Cập nhật các cột phục vụ tính năng khiếu nại và trả hàng.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // 1. Thêm lý do trả hàng (Sử dụng text để khách nhập được nhiều nội dung hơn)
            if (!Schema::hasColumn('orders', 'return_reason')) {
                $table->text('return_reason')->nullable()->after('status')->comment('Lý do trả hàng/khiếu nại từ khách hàng');
            }
            
            // 2. Thêm đường dẫn ảnh minh chứng
            if (!Schema::hasColumn('orders', 'return_image')) {
                $table->string('return_image')->nullable()->after('return_reason')->comment('Đường dẫn ảnh minh chứng khiếu nại');
            }

            // 3. Thêm thời gian yêu cầu trả hàng (Giúp Admin biết khách khiếu nại từ lúc nào)
            if (!Schema::hasColumn('orders', 'return_requested_at')) {
                $table->timestamp('return_requested_at')->nullable()->after('return_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     * Xóa các cột đã thêm khi thực hiện rollback.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = ['return_reason', 'return_image', 'return_requested_at'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};