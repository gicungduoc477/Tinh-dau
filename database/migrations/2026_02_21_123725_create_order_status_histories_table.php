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
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            
            // Liên kết đơn hàng: Khi xóa đơn hàng, lịch sử cũng tự động xóa theo (cascade)
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            
            // Trạng thái cũ và trạng thái mới
            $table->string('from_status')->nullable(); // Có thể null nếu là đơn hàng mới tạo
            $table->string('to_status');
            
            // Người thực hiện thay đổi (Admin hoặc Hệ thống)
            // Nếu xóa user, cột này sẽ chuyển về null thay vì xóa luôn lịch sử (set null)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Ghi chú chi tiết cho sự thay đổi
            $table->text('note')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};