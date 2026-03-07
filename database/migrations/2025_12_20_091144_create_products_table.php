<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id(); // id_sanpham
        $table->string('name'); // ten_sanpham
        $table->string('slug')->unique();
        $table->decimal('price', 10, 2); // gia
        $table->string('image')->nullable(); // hinh_anh
        $table->text('description')->nullable(); // mo_ta
        $table->string('classification')->nullable(); // phan_loai
        $table->integer('stock')->default(0); // so_luong_ton
        
        // Khóa ngoại liên kết với bảng categories (nullable để tránh lỗi khi không có danh mục)
        $table->unsignedBigInteger('category_id')->nullable();
        $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        
        $table->timestamps(); // ngay_them
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
