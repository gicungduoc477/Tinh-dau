<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thay đổi kiểu dữ liệu của cột `return_image` từ `string` sang `text`
     * để có thể lưu nhiều URL ảnh (dưới dạng JSON array).
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Thay đổi cột `return_image` để có thể lưu nhiều ảnh hơn
            // Cần `doctrine/dbal` để chạy: composer require doctrine/dbal
            if (Schema::hasColumn('orders', 'return_image')) {
                $table->text('return_image')->nullable()->comment('JSON array of return image URLs')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     * Hoàn tác lại thay đổi, trả về kiểu `string`.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'return_image')) {
                $table->string('return_image')->nullable()->comment('Đường dẫn ảnh minh chứng khiếu nại')->change();
            }
        });
    }
};
