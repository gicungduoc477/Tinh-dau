<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue; // Có thể dùng để gửi mail xếp hàng (queue)

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Biến order sẽ hiển thị được ở ngoài View (Blade)
     */
    public $order;

    /**
     * Khởi tạo class và nhận dữ liệu đơn hàng.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Định nghĩa tiêu đề Email và thông tin người gửi.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Xác nhận đặt hàng thành công - Mã đơn: #' . ($this->order->order_code ?? 'N/A'),
        );
    }

    /**
     * Định nghĩa giao diện Blade hiển thị nội dung Email.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order_placed', // File: resources/views/emails/order_placed.blade.php
            with: [
                'order' => $this->order,
            ],
        );
    }

    /**
     * Đính kèm tệp nếu cần.
     */
    public function attachments(): array
    {
        return [];
    }
}