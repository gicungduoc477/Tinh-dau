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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // id_hoadon
            
            // 1. Liên kết khách hàng
            $table->unsignedBigInteger('user_id')->nullable(); 
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // 2. Cột kết nối PayOS
            $table->string('order_code')->unique(); 
            
            // 3. Thông tin tài chính & Trạng thái
            $table->decimal('total_price', 15, 2); 
            $table->string('status')->default('pending'); // Trạng thái đơn: pending, shipping, success, cancelled, returning...
            $table->string('payment_status')->default('unpaid'); // Trạng thái tiền: unpaid, paid, refunded... (QUAN TRỌNG cho Dashboard)
            
            // 4. Thông tin nhận hàng
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('phone_number'); 
            $table->text('shipping_address'); 
            
            // 5. THÔNG TIN HOÀN TIỀN (Bổ sung mới cho Hiếu)
            $table->string('bank_name')->nullable();       // Tên ngân hàng khách cung cấp
            $table->string('account_number')->nullable();  // Số tài khoản khách cung cấp
            $table->string('account_holder')->nullable();  // Tên chủ tài khoản (viết hoa không dấu)
            
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};