<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // Liên kết bảng: Mỗi đánh giá thuộc về 1 User và 1 Product
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Nội dung đánh giá
            $table->integer('rating')->default(5); // Lưu từ 1-5 sao
            $table->text('comment')->nullable(); // Nội dung đánh giá của khách
            
            // --- NÂNG CẤP: MEDIA & TAGS ---
            $table->string('image')->nullable(); // URL ảnh chính
            $table->text('image_list')->nullable(); // Lưu JSON danh sách nhiều ảnh
            $table->string('video')->nullable(); // ĐƯỜNG DẪN VIDEO ĐÁNH GIÁ
            $table->json('tags')->nullable(); // CÁC TAG NHANH (Ví dụ: ["Giao hàng nhanh", "Đóng gói kỹ"])

            // --- PHẦN PHẢN HỒI & TƯƠNG TÁC ---
            $table->text('reply')->nullable(); // Nội dung phản hồi công khai
            $table->timestamp('reply_at')->nullable(); // Thời gian phản hồi cuối cùng
            $table->timestamp('first_reply_at')->nullable(); // THỜI GIAN PHẢN HỒI LẦN ĐẦU (Để tính Analytics)
            
            // --- PHẦN QUẢN TRỊ NỘI BỘ ---
            $table->text('admin_note')->nullable(); // Ghi chú nội bộ
            $table->boolean('is_resolved')->default(false); // Trạng thái xử lý
            $table->string('status')->default('active'); // active, pending, hidden
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};