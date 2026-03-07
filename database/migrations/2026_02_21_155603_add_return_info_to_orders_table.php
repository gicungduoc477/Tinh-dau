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
        Schema::table('orders', function (Blueprint $table) {
            // Kiểm tra nếu chưa có cột return_note thì mới thêm
            if (!Schema::hasColumn('orders', 'return_note')) {
                $table->text('return_note')->nullable();
            }

            // Kiểm tra nếu chưa có cột bank_name thì mới thêm
            if (!Schema::hasColumn('orders', 'bank_name')) {
                $table->string('bank_name')->nullable();
            }

            // Kiểm tra nếu chưa có cột account_number thì mới thêm
            if (!Schema::hasColumn('orders', 'account_number')) {
                $table->string('account_number')->nullable();
            }

            // Kiểm tra nếu chưa có cột account_holder thì mới thêm
            if (!Schema::hasColumn('orders', 'account_holder')) {
                $table->string('account_holder')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = ['return_note', 'bank_name', 'account_number', 'account_holder'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};