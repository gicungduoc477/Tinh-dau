<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::table('orders', function (Blueprint $table) {
        if (!Schema::hasColumn('orders', 'return_note')) {
            $table->text('return_note')->nullable();
        }
        // Đảm bảo có đủ các cột cho QR
        if (!Schema::hasColumn('orders', 'bank_name')) $table->string('bank_name')->nullable();
        if (!Schema::hasColumn('orders', 'account_number')) $table->string('account_number')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
