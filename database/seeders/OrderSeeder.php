<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Chạy database seeds để tạo dữ liệu đơn hàng mẫu.
     */
    public function run()
    {
        $userIds = User::pluck('id')->toArray();
        
        if (empty($userIds)) {
            $this->command->error('Vui lòng tạo User trước khi chạy OrderSeeder!');
            return;
        }

        $statuses = ['pending', 'confirmed', 'shipping', 'success', 'canceled'];

        for ($i = 0; $i < 60; $i++) {
            $randomDate = Carbon::now()->subDays(rand(0, 180));
            $randomPhone = '09' . rand(10000000, 99999999);

            // Chỉ giữ lại các trường mà hệ thống của bạn đã xác nhận là có (qua log lỗi trước đó)
            $order = Order::create([
                'user_id'          => $userIds[array_rand($userIds)],
                'name'             => 'Khách hàng mẫu ' . ($i + 1),
                'email'            => 'khachhang' . $i . '@example.com',
                'phone'            => $randomPhone,
                'phone_number'     => $randomPhone,
                'address'          => 'Địa chỉ giả lập số ' . $i,
                'shipping_address' => 'Địa chỉ giao hàng số ' . $i,
                'total_price'      => rand(150, 3000) * 1000,
                'status'           => $statuses[array_rand($statuses)],
                'payment_method'   => 'cod',
                'payment_status'   => 'pending',
                'shipping_method'  => 'standard',
                'shipping_fee'     => 30000,
                // ĐÃ XÓA TRƯỜNG 'note' VÀ 'meta' ĐỂ TRÁNH LỖI COLUMN NOT FOUND
                'created_at'       => $randomDate,
                'updated_at'       => $randomDate,
            ]);

            if ($order->status === 'success') {
                $order->update([
                    'payment_status' => 'paid',
                    'paid_at' => $randomDate
                ]);
            }
        }

        $this->command->info('Đã tạo thành công 60 đơn hàng giả lập!');
    }
}