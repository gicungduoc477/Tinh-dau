<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Hash;

class OrderStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_order_status_update_creates_history_record()
    {
        // 1. Create an admin user and a regular user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $customer = User::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
        ]);

        // 2. Create an order
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => 'pending',
            'total_price' => 100000,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending'
        ]);
        
        $this->assertDatabaseCount('order_status_histories', 0);

        // 3. Authenticate as the admin and send the request to update status
        $response = $this->actingAs($admin)->put(route('admin.orders.updateStatus', $order->id), [
            'status' => 'shipping',
            'note' => 'Đã đóng gói và gửi đi.',
        ]);

        // 4. Assert the response and database state
        $response->assertRedirect(route('admin.orders.show', $order->id));
        $response->assertSessionHas('message', 'Trạng thái đơn hàng đã được cập nhật.');

        // Check if the order status was updated
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipping'
        ]);

        // Check that the history record was created
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'shipping',
            'changed_by' => $admin->id,
            'note' => 'Đã đóng gói và gửi đi.',
        ]);
        
        $this->assertDatabaseCount('order_status_histories', 1);
    }
}
