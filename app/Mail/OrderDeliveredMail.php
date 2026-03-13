<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Khởi tạo class với dữ liệu đơn hàng
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Định nghĩa tiêu đề email
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Đơn hàng #' . $this->order->order_code . ' đã giao thành công!',
        );
    }

    /**
     * Định nghĩa file giao diện (Blade)
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order_delivered',
        );
    }

    /**
     * Đính kèm file nếu cần (tùy chọn)
     */
    public function attachments(): array
    {
        return [];
    }
}