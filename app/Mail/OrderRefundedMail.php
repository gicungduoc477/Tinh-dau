<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderRefundedMail extends Mailable
{
    use Queueable, SerializesModels;

    // Khai báo biến public để có thể sử dụng trực tiếp ngoài View (Blade)
    public $order;

    /**
     * Khởi tạo class và nhận dữ liệu đơn hàng
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Định nghĩa tiêu đề Email (Envelope)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thông báo hoàn tiền thành công - Đơn hàng #' . $this->order->order_code,
        );
    }

    /**
     * Định nghĩa View hiển thị nội dung Email (Content)
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_refunded', // Đảm bảo bạn đã tạo file resources/views/emails/order_refunded.blade.php
        );
    }

    /**
     * Các tệp đính kèm (nếu có)
     */
    public function attachments(): array
    {
        return [];
    }
}