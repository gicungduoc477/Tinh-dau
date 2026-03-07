<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quick_replies', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Tiêu đề mẫu (vd: Cảm ơn khách, Xin lỗi hàng lỗi)
            $table->text('content'); // Nội dung chi tiết của mẫu phản hồi
            $table->integer('usage_count')->default(0); // Thống kê mẫu nào được dùng nhiều nhất
            $table->timestamps();
        });

        // Chèn sẵn một số mẫu cơ bản
        DB::table('quick_replies')->insert([
            [
                'title' => 'Cảm ơn 5 sao',
                'content' => 'Cảm ơn bạn đã tin tưởng ủng hộ shop! Hy vọng sẽ được phục vụ bạn trong những đơn hàng tiếp theo.',
                'created_at' => now()
            ],
            [
                'title' => 'Xin lỗi sự cố',
                'content' => 'Rất tiếc vì trải nghiệm không tốt của bạn. Shop đã nhắn tin riêng, bạn vui lòng kiểm tra inbox để shop giải quyết ngay ạ!',
                'created_at' => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('quick_replies');
    }
};