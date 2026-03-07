<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShippingMail extends Mailable
{
    use Queueable, SerializesModels;

    // Khai báo biến public để truyền dữ liệu sang View
    public $order;

    /**
     * Khởi tạo class và nhận dữ liệu đơn hàng từ Controller
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Cấu hình Tiêu đề Mail (Envelope)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Đơn hàng #' . $this->order->order_code . ' đang được giao đến bạn',
        );
    }

    /**
     * Cấu hình Giao diện hiển thị (Content)
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_shipping', // File này nằm tại resources/views/emails/order_shipping.blade.php
        );
    }

    /**
     * Các tệp đính kèm (Ví dụ: hóa đơn PDF nếu có)
     */
    public function attachments(): array
    {
        return [];
    }
}