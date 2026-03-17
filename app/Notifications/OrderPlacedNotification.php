<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    protected $order;
    protected $type; // Thêm type để phân biệt loại thông báo

    /**
     * Khởi tạo Notification
     * @param $order : Dữ liệu đơn hàng
     * @param string $type : 'placed' (đặt mới) hoặc 'updated' (cập nhật trạng thái)
     */
    public function __construct($order, $type = 'placed')
    {
        $this->order = $order;
        $this->type = $type;
    }

    /**
     * Gửi vào Database để hiện ở chuông thông báo
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Dữ liệu lưu vào bảng 'notifications' (Cột data)
     */
    public function toArray($notifiable)
    {
        // Tùy biến lời nhắn dựa trên loại thông báo
        $message = ($this->type === 'placed') 
            ? 'Đơn hàng mới ' . $this->order->order_code . ' đã được đặt thành công.'
            : 'Đơn hàng ' . $this->order->order_code . ' của bạn vừa được cập nhật trạng thái: ' . $this->order->status;

        return [
            'order_id'   => $this->order->id,
            'order_code' => $this->order->order_code,
            'status'     => $this->order->status,
            'message'    => $message,
            // Link dẫn thẳng tới trang chi tiết đơn hàng của khách
            'url'        => route('orders.show', $this->order->id), 
            'total'      => number_format($this->order->total_price, 0, ',', '.') . 'đ',
            'type'       => $this->type,
            'created_at' => now()->format('H:i d/m/Y'),
        ];
    }
}